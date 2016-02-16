<?php
// Page for recovering a user account
// URL: /recover/ => /recover_account_input.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");

if (IsMaintenanceMode()) PostBanner("Site is in read-only mode, account recovery has been disabled", "red", false);

if (isset($user)) {
    Redirect("/");
}

if (isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['password-confirm']) &&
    !IsMaintenanceMode()) {
    $vars['email'] = $_POST['email'];
    $email = mb_strtolower($_POST['email'], "UTF-8");
    $password = $_POST['password'];
    $password_confirm = $_POST['password-confirm'];
    if ($password == $password_confirm) {
        $escaped_email = sql_escape($email);
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(Email)=UPPER('$escaped_email') AND Usermode=1 AND Password<>'';", 1)) {
            $usr = $result->fetch_assoc();
            $username = $usr['Username'];
            // Double-check imported account status. Shouldn't have empty password, but check anyways.
            if (!startsWith($username, IMPORTED_ACCOUNT_USERNAME_PREFIX)) {
                $redirect = "/recover/success/";
                $code = CreateCodeEntry($email, "account_recovery", $usr['UserId'].",".$email.",".md5($password), $redirect);
                if ($code !== FALSE) {
                    $uid = $usr['UserId'];
                    $ip = $_SERVER['REMOTE_ADDR'];
                    if (SendRecoveryEmail($email, $username, false, true, $code)) {
                        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> requested a password reset from IP $ip", "");
                        $_SESSION['recovery_email'] = $email;
                        Redirect("/recover/confirm/");
                    } else {
                        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> requested a password reset from IP $ip, but email failed to send", "");
                        PostBanner("Error sending confirmation email, please try again later", "red");
                    }
                } else {
                    PostBanner("Unexpected error, please try again later", "red");
                }
            } else {
                // Should never happen.
                PostBanner("Unable to recover password for account that has not been logged into before. Please contact an administrator for assistance", "red");
            }
        } else {
            PostBanner("Account not found", "red");
        }
    } else {
        PostBanner("Password does not match", "red");
    }
}
RenderPage("user/recover.tpl");
return;

?>