<?php
// Page for telling the user their account email/password was changed.
// URL: /recover/success/ => /recover_account_success.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");

if (isset($_SESSION['auth_row'])) {
    $data = $_SESSION['auth_row'];
    unset($_SESSION['auth_row']);
    unset($_SESSION['recovery_email']);
    $params = explode(",", $data['Data']);
    if (sizeof($params) != 3) RenderErrorPage("Unexpected error, please try again later");
    $uid = $params[0];
    $email = $params[1];
    $password_md5 = $params[2];
    sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$uid;", 1) or RenderErrorPage("Account not found");
    $usr = $result->fetch_assoc();
    $email_changed = $usr['Email'] != $email;
    $pass_changed = $usr['Password'] != $password_md5;
    if ($email_changed && $pass_changed) {
        $detailed_desc = "email and password";
    } else if ($email_changed) {
        $detailed_desc = "email";
    } else if ($pass_changed) {
        $detailed_desc = "password";
    }
    ChangeEmailPassword($uid, $email, $password_md5) or RenderErrorPage("Failed to change $detailed_desc, please try again later");
    // TODO: Re-compute headers.
    RenderPage("user/recover_success.tpl");
    return;
} else {
    header("Location: /recover/confirm/");
    exit();
}
return;
?>