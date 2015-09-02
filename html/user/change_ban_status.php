<?php
// PHP page for receiving actions to change a user's ban status.
// URL: /user/{user-id}/ban/
// URL: /user/change_ban_status.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");
$profile_user = &$vars['profile']['user'];
$puid = $profile_user['UserId'];

if (!isset($_POST['action'])) {
    InvalidURL();
    return;
}
if (!isset($user)) {
    RenderErrorPage("Must be logged in to access this page");
    return;
}
if (!CanUserBan($user, $profile_user)) {
    RenderErrorPage("Access denied");
    return;
}

$action = $_POST['action'];
switch ($action) {
    case "permban":
        $escaped_reason = sql_escape(SanitizeHTMLTags($_POST['reason'], ""));  // Remove tags.
        if (sql_query("UPDATE ".USER_TABLE." SET Usermode=-1, BanReason='$escaped_reason', BanExpireTime=-1 WHERE UserId=$puid;")) {
            PostSessionBanner("User permanently banned", "green");
        } else {
            PostSessionBanner("Failed to set permanent ban", "red");
        }
        break;
    case "tempban":
        $escaped_reason = sql_escape(SanitizeHTMLTags($_POST['reason'], ""));  // Remove tags.
        if (isset($_POST['duration']) && is_numeric($_POST['duration'])) {
            $expire_time = time() + (int)$_POST['duration'];
            if (sql_query("UPDATE ".USER_TABLE." SET Usermode=-1, BanReason='$escaped_reason', BanExpireTime=$expire_time WHERE UserId=$puid;")) {
                PostSessionBanner("User banned for ".FormatDuration((int)$_POST['duration']), "green");
            } else {
                PostSessionBanner("Failed to set temporary ban", "red");
            }
        } else {
            PostSessionBanner("Invalid temporary ban duration", "red");
        }
        break;
    case "unban":
        if (sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$puid;")) {
            PostSessionBanner("User ban lifted", "green");
        } else {
            PostSessionBanner("Failed to lift ban", "red");
        }
        break;
    default:
        // Do nothing.
        break;
}

header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>