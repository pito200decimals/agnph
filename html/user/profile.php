<?php
// General account profile page.
// URL: /user/{user-id}/
// URL: /user/profile.php?uid={user-id}

// Site includes, including login authentication.
include_once("../header.php");

if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    RenderErrorPage("Profile not found.");
    return;
}
$profile_id = (int)$_GET['uid'];

$profile_users = array();
LoadTableData(array(USER_TABLE), "UserId", array($profile_id), $profile_users) or RenderErrorPage("Profile not found.");
$profile_user = $profile_users[$profile_id];

$vars['profile']['user'] = $profile_user;
if (isset($user)) {
    $vars['user'] = $user;
}
// This is how to output the template.
RenderPage("user/profile.tpl");
return;
?>