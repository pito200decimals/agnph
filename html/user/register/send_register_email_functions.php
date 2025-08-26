<?php
// Utility functions for sending a registration email.

include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");

function CheckSuperAdminPerms() {
    global $user;
    if (!isset($user)) {
        // Must be logged in.
        return false;
    }
    if (!contains($user['Permissions'], 'A')) {
        // Must be super-admin.
        return false;
    }
    return true;
}

function SendValidationEmailLink($uid, $username, $email, $joinTime) {
    if (IsMaintenanceMode()) MaintenanceError();
    $to = "$username <$email>";
    $subject = "Account Registration for AGNPH";
    // Don't have to worry about duplicate codes, as only one account can exist per-email.
    $code = CreateCodeEntry($email, "registration", "$uid", "/register/success/", REGISTER_ACCOUNT_TIMESTAMP_DURATION);
    if ($code === FALSE) return false;
    $url = GetAuthURL($code);
    $time = REGISTER_ACCOUNT_HUMAN_READABLE_STRING;
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
    $resend_link = " <a href='/admin/register/resend/?uid=$uid' title='Only works if within ".REGISTER_ACCOUNT_HUMAN_READABLE_STRING." of original registration'>Resend email</a>";
    if ($success) {
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> registration email sent ($code).$resend_link", "");
    } else {
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> registration email <strong>NOT</strong> sent ($code).$resend_link", "");
    }
    return $success;
}


?>
