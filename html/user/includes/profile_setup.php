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
if (!LoadAllUserPreferences($profile_id, $profile_user, true)) RenderErrorPage("Error loading profile");
$profile_user['avatarURL'] = GetAvatarURL($profile_user);
switch ($profile_user['Gender']) {
    case 'U':
        break;
    case 'M':
        $profile_user['gender'] = "Male";
        break;
    case 'F':
        $profile_user['gender'] = "Female";
        break;
    case 'O':
        $profile_user['gender'] = "Other";
        break;
    default:
        break;
}
$vars['profile']['user'] = $profile_user;
?>