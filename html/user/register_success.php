<?php
// Register a new account page.
// URL: /register/
// URL: /user/register.php

define("DEBUG", true);

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."user/includes/register_functions.php");

if (isset($user)) {
    header("Location: /");
    return;
}
if (isset($_GET['key'])) {
    sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE Usermode=0;", 1) or RenderErrorPage("Error validating email, registration link could have expired");
    $registered_user = null;
    while ($row = $result->fetch_assoc()) {
        $auth = HashAuthKey($row['UserName'], $row['Email'], $row['JoinTime']);
        if ($auth == $_GET['key']) {
            $registered_user = $row;
            break;
        }
    }
    if ($registered_user != null) {
        // Found user to authenticate.
        PrepareAllUserTables($registered_user) or RenderErrorPage("Failed to complete registration. Please contact an AGNPH administrator for help");
        // Log in user.
        // Set $user to prevent login attempt when including login.php
        $user = array();
        include(SITE_ROOT."includes/auth/login.php");
        ForceLogin($registered_user['UserId']);
        //header("Location: /");
        debug("SUCCESS!");
        return;
    }
}


$vars['email'] = $_SESSION['register_email'];
// This is how to output the template.
RenderPage("user/register_success.tpl");
return;

function PrepareAllUserTables($user) {
    $success = true;
    $uid = $user['UserId'];

    // Create bio file.
    mkdirs("/user/data/bio/");
    $success = write_file(SITE_ROOT."user/data/bio/$uid.txt", "");
    debug("WRITING TO: '".SITE_ROOT."user/data/bio/$uid.txt'");
    if ($success) debug("WRITE FILE SUCCESS!");
    else debug("WRITE FILE FAILURE");

    // Create Forums table entry.
    if ($success) $success = sql_query("INSERT INTO ".FORUMS_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Gallery table entry.
    if ($success) $success = sql_query("INSERT INTO ".GALLERY_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    // Create Fics table entry.
    if ($success) $success = sql_query("INSERT INTO ".FICS_USER_PREF_TABLE." (UserId) VALUES ($uid);");

    if ($success) $success = sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$uid;");

    // TODO: Create Oekaki entry
    // TODO: Log action
    return $success;
}
?>