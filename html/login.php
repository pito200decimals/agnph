<?php
// Page for logging in to a user's account.
// URL: /login/ => /login.php

include ("header.php");

if (IsMaintenanceMode()) PostBanner("Site is in read-only mode, login has been disabled", "red", false);

if (isset($_POST['username']) && isset($_POST['password'])) {
    if (contains($_POST['username'], "-")) {  // Prevent logging in with imported accounts.
        PostBanner("Invalid username/password", "red");
        RenderPage("login.tpl");
        return;
    }
    include_once(SITE_ROOT."includes/auth/login.php");
    if (isset($user)) {
        if (isset($newly_imported) && $newly_imported) {
            // Show a splash page notifying to check their email settings, and not to log in to another account.
            Redirect("/user/import/");
        }
        Redirect("/");
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
    Redirect("/");
}

$vars['login_msg'] = SanitizeHTMLTags(GetSiteSetting(LOGIN_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);

RenderPage("login.tpl");
return;
?>