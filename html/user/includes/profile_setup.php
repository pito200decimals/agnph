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
LoadAllUserPreferences($profile_id, $profile_user, true);
$profile_user['avatarURL'] = GetAvatarURL($profile_user);
$vars['profile']['user'] = $profile_user;
?>