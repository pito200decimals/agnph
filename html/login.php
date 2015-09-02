<?php
// Page for logging in to a user's account.
// URL: /login/ => /login.php

define("SITE_ROOT",  __DIR__."/");

// TODO: Remove after site testing is complete.
if (isset($_GET['debug']) && $_GET['debug'] == true) {
    $_POST['username'] = "User1";
    $_POST['password'] = "Password 1";
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    include_once(SITE_ROOT."includes/auth/login.php");
    if (isset($user)) {
        header("Location: /");
        exit();
    } else {
        if (isset($user_banned) && $user_banned) {
            $msg = "Your account has been banned";
            if (isset($user_ban_timestamp) && $user_ban_timestamp != -1) {
                // For temporary bans, show time remaining.
                $duration = FormatDuration($user_ban_timestamp - time());
                $msg .= " for $duration";
            }
            if (isset($user_ban_reason)) {
                $msg .= "<br />";
                $msg .= "Reason: $user_ban_reason";
            }
            PostBanner($msg, "red", true/*dismissable*/, true/*noescape*/);
        } else {
            PostBanner("Invalid username/password", "red");
        }
        $vars['username'] = $_POST['username'];
    }
} else {
    include_once("header.php");
}

if (isset($user)) {
    header("Location: /");
    exit();
}

RenderPage("login.tpl");
return;
?>