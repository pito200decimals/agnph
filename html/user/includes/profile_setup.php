<?php
// General setup stuff for loading a user profile. Included for all user pages.
// Include after header is included.

include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/user.php");

if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    RenderErrorPage("Profile not found");
    return;
}
$profile_id = (int)$_GET['uid'];

$profile_users = array();
LoadTableData(array(USER_TABLE), "UserId", array($profile_id), $profile_users) or RenderErrorPage("Profile not found.");
$profile_user = $profile_users[$profile_id];
$vars['profile']['user'] = $profile_user;
?>