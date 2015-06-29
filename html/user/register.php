<?php
// Register a new account page.
// URL: /register/
// URL: /user/register.php

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/date.php");
include_once(SITE_ROOT."user/includes/register_functions.php");

if (isset($user)) {
    header("Location: /");
    return;
}
$vars['banner_nofications'] = array();
if (isset($_POST['username']) &&
    isset($_POST['email']) &&
    isset($_POST['email-confirm']) &&
    isset($_POST['password']) &&
    isset($_POST['password-confirm']) &&
    isset($_POST['bday']) &&
    isset($_POST['captcha'])) {
    $success = true;
    $username = mb_strtolower($_POST['username']);
    if (strlen($username) < MIN_USERNAME_LENGTH) {
        ShowErrorBanner("Username too short");
        $success = false;
    } else if (strlen($username) > MAX_USERNAME_LENGTH) {
        ShowErrorBanner("Username too long");
        $success = false;
    } else if (!mb_ereg("^[a-z0-9_]+$", $username)) {
        ShowErrorBanner("Username must consist of a-z, 0-9, or _");
        $success = false;
    }
    $email = $_POST['email'];
    if ($email != $_POST['email-confirm']) {
        ShowErrorBanner("Mismatched email addresses");
        $success = false;
    }
    // Email regex taken from http://www.regular-expressions.info/email.html
    if (!mb_eregi("^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$", $email) &&
        !mb_eregi("^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$", $email)) {
        ShowErrorBanner("Invalid email address");
        $success = false;
    }
    $password = $_POST['password'];
    if ($password !== $_POST['password-confirm']) {
        ShowErrorBanner("Mismatched passwords");
        $success = false;
    }
    $bday = ParseDate($_POST['bday']);
    if ($bday == null) {
        ShowErrorBanner("Invalid birthday");
        $success = false;
    }
    $captcha = $_POST['captcha'];
    if (!isset($_SESSION['captcha_code'])) {
        ShowErrorBanner("Form expired, please try again");
        $success = false;
    } else if ($captcha !== $_SESSION['captcha_code']) {
        ShowErrorBanner("Captcha did not match");
        $success = false;
    }

    // Do database checks for username/email.
    $escaped_username = sql_escape($username);
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserName='$escaped_username' LIMIT 1;", 1)) {
        // Exists a duplicate username.
        ShowErrorBanner("Username already taken.");
        $success = false;
    }
    $escaped_email = sql_escape($email);
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(Email)=UPPER('$escaped_email') And RegisterIP<>'' LIMIT 1;", 1)) {
        // Exists a duplicate email on a non-imported account.
        ShowErrorBanner("Email address already in use.");
        $success = false;
    }

    if ($success) {
        if (HandlePostSuccess($username, $email, $password, $bday)) {
            $_SESSION['register_email'] = $email;
            header("Location: /register/success/");
            return;
        }
    }
    $vars['username'] = $username;
    $vars['email'] = $email;
    $vars['bday'] = $_POST['bday'];
} else {
    $vars['bday'] = "mm/dd/yyyy";
}

if (sql_query_into($result, "SELECT * FROM ".SITE_TEXT_TABLE." WHERE Name='RegisterDisclaimer';", 1)) {
    $vars['RegisterDisclaimer'] = $result->fetch_assoc()['Text'];
}

// This is how to output the template.
RenderPage("user/register.tpl");
return;

function ShowErrorBanner($msg) {
    global $vars;
        $vars['banner_nofications'][] = array(
            "strong" => true,
            "classes" => array("red-banner"),
            "text" => $msg,
            "dismissable" => true);
}

function HandlePostSuccess($username, $email, $password, $bday) {
    // Create user table values.
    $escaped_username = sql_escape($username);
    $escaped_email = sql_escape($email);
    $hashed_password = md5($password);
    $escaped_password = sql_escape($hashed_password);
    $escaped_bday = sql_escape($bday);  // Not necessary, but just in case...
    $register_time = time();
    $escaped_ip = sql_escape($_SERVER['REMOTE_ADDR']);
    $success = sql_query("INSERT INTO ".USER_TABLE."
        (UserName, DisplayName, Email, Password, DOB, JoinTime, RegisterIP)
        VALUES
        ('$escaped_username', '$escaped_username', '$escaped_email', '$escaped_password', '$escaped_bday', $register_time, '$escaped_ip');");
    if ($success) {
        $uid = sql_last_id();
        $interval = REGISTER_ACCOUNT_EXPIRE_TIME;
        $success = sql_query("
            CREATE EVENT delete_expired_register_account_$uid
            ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL $interval DO
            DELETE FROM ".USER_TABLE." WHERE UserId=$uid AND Usermode=0;");
    }
    if ($success) $success = SendValidationEmailLink($username, $email, $register_time);
    // TODO: Log action
    if (!$success) ShowErrorBanner("Error creating account, please try again");
    return $success;
}

function SendValidationEmailLink($username, $email, $joinTime) {
    $to = "$username <$email>";
    // TODO: Remove (TEST)
    $subject = "(TEST) Account Registration for AGNPH";
    $auth = HashAuthKey($username, $email, $joinTime);
    $url = "http:/agnph.cloudapp.net/register/success/?key=$auth";
    $time = REGISTER_ACCOUNT_EXPIRE_TIME_READABLE_STRING;
    $message = <<<EOT
<html>
    <head>
        <title>
            $subject
        </title>
    </head>
    <body>
        <p>
            Thank you for joining AGNPH! An account with username <strong>$username</strong> has been registered with this email address (<strong>$email</strong>). Please click the link below within $time to complete your account registration:
        </p>
        <p>
            <a href="$url">$url</a>
        </p>
        <p>
            If you did not request to join AGNPH, please disregard this message.
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