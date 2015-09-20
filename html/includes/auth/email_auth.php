<?php
// General helper functions to send/authenticate email codes. Also includes user-password changing functions.

function RandomString($length=10, $charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") {
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= $charset[mt_rand(0, strlen($charset) - 1)];
    }
    return $str;
}

// Creates an auth code and adds the table entry. Returns FALSE on error.
function CreateCodeEntry($email, $type, $data, $redirect, $expire_time = DEFAULT_EMAIL_EXPIRE_TIMESTAMP_DURATION) {
    $code = RandomString();
    $now = time();
    $max_timestamp = $now + $expire_time;
    $escaped_email = sql_escape($email);
    $escaped_type = sql_escape($type);
    $escaped_data = sql_escape($data);
    $escaped_redirect = sql_escape($redirect);
    $success = sql_query("INSERT INTO ".SECURITY_EMAIL_TABLE."
        (Email, Timestamp, MaxTimestamp, Code, Type, Data, Redirect)
        VALUES
        ('$escaped_email', $now, $max_timestamp, '$code', '$escaped_type', '$escaped_data', '$escaped_redirect');");
    if (!$success) return false;
    return $code;
}

function GetAuthURL($code) {
    $arg = urlencode($code);
    return SITE_DOMAIN."/auth/?code=$arg";
}

function ValidateEmail($email) {
    // Email regex taken from http://www.regular-expressions.info/email.html
    if (strlen($email) > MAX_USER_EMAIL_LENGTH) return false;
    if (!mb_eregi("^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$", $email) &&
        !mb_eregi("^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$", $email)) {
        return false;
    }
    // Also check if DNS MX entry exists.
    list($usr, $domain) = explode("@", $email);
    if (!checkdnsrr($domain, "MX")) {
        return false;
    }
    return true;
}

function ChangeEmailPassword($uid, $email, $password_md5, $confirm_email = true, $re_login = true) {
    if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$uid AND Usermode=1;", 1)) return false;
    $usr = $result->fetch_assoc();
    if ($usr['Email'] != $email && $confirm_email) {
        // Ensure sending email works.
        if (!SendEmailChangeConfirmationEmail($email, $usr['UserName'])) return false;
    }
    $escaped_email = sql_escape($email);
    if (!sql_query("UPDATE ".USER_TABLE." SET Email='$escaped_email', Password='$password_md5' WHERE UserId=$uid;")) return false;
    if ($re_login) {
        ForceLogin($uid);
    }
    return true;
}

function ForceLogin($uid) {
    global $user;
    $escaped_uid = sql_escape($uid);
    if (!sql_query_into($result, "SELECT UserID,UserName,Email,Password FROM ".USER_TABLE." WHERE UserId='$escaped_uid' LIMIT 1;", 1)) {
        return false;
    }
    $user = $result->fetch_assoc();
    $uid = $user['UserID'];
    $email = $user['Email'];
    $encryptedPassword = $user['Password'];

    $salt = md5($email.$encryptedPassword);
    if (AuthenticateUser($uid, $salt)) {
        setcookie(UID_COOKIE, $uid, time() + COOKIE_DURATION, "/");
        setcookie(SALT_COOKIE, $salt, time() + COOKIE_DURATION, "/");
        FetchUserHeaderVars();  // Re-fetch user header vars.
        return true;
    } else {
        // Cookies are unset by AuthenticateUser().
        return false;
    }
}

function SendEmailChangeConfirmationEmail($email, $username) {
    $to = "$username <$email>";
    // TODO: Remove (TEST)
    $subject = "(TEST) Confirmation: AGNPH Email address updated";
    $time = DEFAULT_EMAIL_EXPIRE_HUMAN_READABLE_STRING;
    $message = <<<EOT
<html>
    <head>
        <title>
            $subject
        </title>
    </head>
    <body>
        <p>
            $username,
        </p>
        <p>
            This message is to confirm that your account at AGNPH has been updated to use this email (<strong>$email</strong>). All further notifications from AGNPH will be sent to this address.
        </p>
    </body>
</html>
EOT;

    $headers = <<<EOT
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
From: AGNPH <do-not-reply@agn.ph>
EOT;

    $success = mail($to, $subject, $message, $headers);
    return $success;
}

function SendRecoveryEmail($email, $username, $email_change, $password_change, $code) {
    $to = "$username <$email>";
    if ($email_change) {
        if ($password_change) {
            $changed = "email/password";
            $detailed_change = "email and password";
        } else {
            $changed = "email";
            $detailed_change = "email";
        }
    } else {
        if ($password_change) {
            $changed = "password";
            $detailed_change = "password";
        } else {
            return false;
        }
    }
    // TODO: Remove (TEST)
    $subject = "(TEST) Confirmation: AGNPH Account $changed change";
    $url = GetAuthURL($code);
    $time = DEFAULT_EMAIL_EXPIRE_HUMAN_READABLE_STRING;
    $message = <<<EOT
<html>
    <head>
        <title>
            $subject
        </title>
    </head>
    <body>
        <p>
            $username,
        </p>
        <p>
            This notification is to confirm that you requested your $detailed_change be changed for your account at AGNPH. Please click the link below to complete this change.
        </p>
        <p>
            <a href="$url">$url</a>
        </p>
        <p>
            If you did not request your account $detailed_change changed, please disregard this email.
        </p>
    </body>
</html>
EOT;

    $headers = <<<EOT
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
From: AGNPH <do-not-reply@agn.ph>
EOT;

    $success = mail($to, $subject, $message, $headers);
    return $success;
}

?>