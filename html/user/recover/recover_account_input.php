<?php
// Page for recovering a user account
// URL: /recover/ => /recover_account_input.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");

if (IsMaintenanceMode()) PostBanner("Site is in read-only mode, account recovery has been disabled", "red", false);

if (isset($user)) {
    header("Location: /");
    exit();
}

if (isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['password-confirm'])) {
    if (!CanPerformSitePost()) MaintenanceError();
    $vars['email'] = $_POST['email'];
    $email = mb_strtolower($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password-confirm'];
    if ($password == $password_confirm) {
        $escaped_email = sql_escape($email);
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(Email)=UPPER('$escaped_email') AND Usermode=1;", 1)) {
            $usr = $result->fetch_assoc();
            $username = $usr['Username'];
            $redirect = "/recover/success/";
            // TODO: Logging.
            $code = CreateCodeEntry($email, "account_recovery", $usr['UserId'].",".$email.",".md5($password), $redirect);
            if ($code !== FALSE) {
                if (SendRecoveryEmail($email, $username, false, true, $code)) {
                    $_SESSION['recovery_email'] = $email;
                    header("Location: /recover/confirm/");
                    exit();
                } else {
                    PostBanner("Error sending confirmation email, please try again later", "red");
                }
            } else {
                PostBanner("Unexpected error, please try again later", "red");
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