<?php
// Page for recovering a user account
// URL: /recover/ => /recover_account_input.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");

if (isset($user)) {
    header("Location: /");
    return;
}

if (isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['password-confirm'])) {
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
                    return;
                } else {
                    $vars['error'] = "Error sending confirmation email, please try again later";
                }
            } else {
                $vars['error'] = "Unexpected error, please try again later";
            }
        } else {
            $vars['error'] = "Account not found";
        }
    } else {
        $vars['error'] = "Password does not match";
    }
}
RenderPage("user/recover.tpl");
return;

?>