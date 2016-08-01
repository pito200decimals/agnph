<?php
// Register a new account page, after email authentication.
// URL: /register/success/
// URL: /user/register/register_success.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");

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

function PrepareAllUserTables($usr) {
    $success = true;
    $uid = $usr['UserId'];

    // Create bio file.
    mkdirs("/user/data/bio/");  // Make sure directory is created.
    $bio_path = SITE_ROOT."user/data/bio/$uid.txt";
    $success = write_file($bio_path, "");

    // Create Forums table entry.
    if ($success) $success = sql_query("INSERT INTO ".FORUMS_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Gallery table entry.
    if ($success) $success = sql_query("INSERT INTO ".GALLERY_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Fics table entry.
    if ($success) $success = sql_query("INSERT INTO ".FICS_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Oekaki table entry.
    if ($success) $success = sql_query("INSERT INTO ".OEKAKI_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Mark user as registered.
    if ($success) $success = sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$uid;");

    if ($success) {
        // Note: Username and display name are the same at this point when activating a newly-registered account.
        $username = $usr['DisplayName'];
        $ip = $usr['RegisterIP'];
        global $user;
        $user = array("UserId" => $usr['UserId']);
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> activated new account", "");
        unset($user);
    } else {
        // Try to delete files/table entries that were created.
        delete_files($bio_path);
        sql_query("DELETE FROM ".FORUMS_USER_PREF_TABLE." WHERE UserId=$uid;");
        sql_query("DELETE FROM ".GALLERY_USER_PREF_TABLE." WHERE UserId=$uid;");
        sql_query("DELETE FROM ".FICS_USER_PREF_TABLE." WHERE UserId=$uid;");
        sql_query("DELETE FROM ".OEKAKI_USER_PREF_TABLE." WHERE UserId=$uid;");
        // User table remains unregistered, just leave it so user can try again.
    }
    return $success;
}
?>