<?php
// General account profile page.
// URL: /user/{user-id}/
// URL: /user/profile.php?uid={user-id}

// Site includes, including login authentication.
include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");

if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    RenderErrorPage("Profile not found.");
    return;
}
$profile_id = (int)$_GET['uid'];

$profile_users = array();
LoadTableData(array(USER_TABLE), "UserId", array($profile_id), $profile_users) or RenderErrorPage("Profile not found.");
$profile_user = $profile_users[$profile_id];
$vars['profile']['user'] = $profile_user;

// Read in bio text file.
$uid = $profile_user['UserId'];
$file_path = SITE_ROOT."user/bio/$uid.txt";
read_file($file_path, $bio_contents) or RenderErrorPage("Error loading profile.");
$vars['profile']['user']['bio'] = $bio_contents;

// This is how to output the template.
RenderPage("user/profile.tpl");
return;
?>