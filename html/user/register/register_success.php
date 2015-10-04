<?php
// Register a new account page, after email authentication.
// URL: /register/success/
// URL: /user/register/register_success.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");
include_once(SITE_ROOT."user/includes/register_functions.php");

if (isset($user)) {
    Redirect("/");
    return;
}
if (isset($_SESSION['auth_row'])) {
    $data = $_SESSION['auth_row'];
    unset($_SESSION['auth_row']);
    unset($_SESSION['register_email']);
    $uid = $data['Data'];
    sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$uid AND Usermode=0;", 1) or RenderErrorPage("Error validating email, registration link could have expired");
    $registered_user = $result->fetch_assoc();
    PrepareAllUserTables($registered_user) or RenderErrorPage("Failed to complete registration. Please contact an AGNPH administrator for help");
    ForceLogin($uid);
    Redirect("/");
} else {
    Redirect("/register/confirm/");
}
return;

function PrepareAllUserTables($user) {
    $success = true;
    $uid = $user['UserId'];

    // Create bio file.
    mkdirs("/user/data/bio/");
    $success = write_file(SITE_ROOT."user/data/bio/$uid.txt", "");

    // Create Forums table entry.
    if ($success) $success = sql_query("INSERT INTO ".FORUMS_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Gallery table entry.
    if ($success) $success = sql_query("INSERT INTO ".GALLERY_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Fics table entry.
    if ($success) $success = sql_query("INSERT INTO ".FICS_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // TODO: Create Oekaki entry

    // Mark user as registered.
    if ($success) $success = sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$uid;");

    if ($success) {
        // Note: Username and display name are the same at this point when activating a newly-registered account.
        $username = $user['DisplayName'];
        $ip = $user['RegisterIP'];
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> activated new account", "");
    }
    return $success;
}
?>