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
if ($profile_user['HideOnlineStatus']) {
    // Always show as offline.
    $profile_user['online'] = false;
} else {
    // Check more accurate visit table.
    if (sql_query_into($result, "SELECT VisitTime FROM ".USER_VISIT_TABLE." WHERE GuestId='$profile_id';", 1)) {
        $profile_user['LastVisitTime'] = $result->fetch_assoc()['VisitTime'];
    }
    if ($profile_user['LastVisitTime'] + CONSIDERED_ONLINE_DURATION > time()) {
        $profile_user['online'] = true;
    }
}
switch ($profile_user['Gender']) {
    case 'U':
        $profile_user['gender'] = "";
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
// Also construct a name with underscores, for later use.
$profile_user['underscore_name'] = mb_ereg_replace("\s+", "_", $profile_user['DisplayName']);
$vars['profile']['user'] = $profile_user;
?>