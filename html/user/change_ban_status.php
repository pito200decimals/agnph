<?php
// PHP page for receiving actions to change a user's ban status.
// URL: /user/{user-id}/ban/
// URL: /user/change_ban_status.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/date.php");
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
        $escaped_reason = sql_escape(GetSanitizedTextTruncated($_POST['reason'], NO_HTML_TAGS, MAX_BAN_REASON_LENGTH));
        if (sql_query("UPDATE ".USER_TABLE." SET Usermode=-1, BanReason='$escaped_reason', BanExpireTime=-1 WHERE UserId=$puid;")) {
            PostSessionBanner("User permanently banned", "green");
            $uid = $user['UserId'];
            $username = $user['DisplayName'];
            $puid = $profile_user['UserId'];
            $pusername = $profile_user['DisplayName'];
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> permanently banned user <strong><a href='/user/$puid/'>$pusername</a></strong>", "");
        } else {
            PostSessionBanner("Failed to set permanent ban", "red");
        }
        break;
    case "tempban":
        $escaped_reason = sql_escape(GetSanitizedTextTruncated($_POST['reason'], NO_HTML_TAGS, MAX_BAN_REASON_LENGTH));
        if (isset($_POST['duration']) && is_numeric($_POST['duration'])) {
            $expire_time = time() + (int)$_POST['duration'];
            if (sql_query("UPDATE ".USER_TABLE." SET Usermode=-1, BanReason='$escaped_reason', BanExpireTime=$expire_time WHERE UserId=$puid;")) {
                $duration = FormatDuration((int)$_POST['duration']);
                PostSessionBanner("User banned for $duration", "green");
                $uid = $user['UserId'];
                $username = $user['DisplayName'];
                $puid = $profile_user['UserId'];
                $pusername = $profile_user['DisplayName'];
                LogAction("<strong><a href='/user/$uid/'>$username</a></strong> temporarily banned user <strong><a href='/user/$puid/'>$pusername</a></strong> ($duration)", "");
            } else {
                PostSessionBanner("Failed to set temporary ban", "red");
            }
        } else {
            PostSessionBanner("Invalid temporary ban duration", "red");
        }
        break;
    case "underageban":
        $ban_expire_date = Get18YearsLaterDateStr($profile_user['DOB']);
        $expire_time = strtotime($ban_expire_date);
        if ($expire_time === FALSE) {
            PostSessionBanner("Failed to set ban", "red");
            break;
        }
        if ($expire_time < time()) {
            PostSessionBanner("Failed to set ban (ban expire date set in the past)", "red");
            break;
        }
        $escaped_reason = "User is not 18+ years old.";
        if (sql_query("UPDATE ".USER_TABLE." SET Usermode=-1, BanReason='$escaped_reason', BanExpireTime=$expire_time WHERE UserId=$puid;")) {
            $duration = FormatDuration((int)$_POST['duration']);
            PostSessionBanner("User banned until $ban_expire_date", "green");
            $uid = $user['UserId'];
            $username = $user['DisplayName'];
            $puid = $profile_user['UserId'];
            $pusername = $profile_user['DisplayName'];
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> banned user <strong><a href='/user/$puid/'>$pusername</a></strong> due to underage status (expires $ban_expire_date)", "");
        } else {
            PostSessionBanner("Failed to set temporary ban", "red");
        }
        break;
    case "unban":
        if (sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$puid;")) {
            $uid = $user['UserId'];
            $username = $user['DisplayName'];
            $puid = $profile_user['UserId'];
            $pusername = $profile_user['DisplayName'];
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> manually lifted ban on user <strong><a href='/user/$puid/'>$pusername</a></strong>", "");
            PostSessionBanner("User ban lifted", "green");
        } else {
            PostSessionBanner("Failed to lift ban", "red");
        }
        break;
    case "delete":
        $escaped_reason = "Deleted";
        $new_username = DELETED_ACCOUNT_USERNAME_PREFIX.$profile_user['UserName'];
        $new_email = DELETED_ACCOUNT_EMAIL_PREFIX.$profile_user['Email'];
        if (sql_query("UPDATE ".USER_TABLE." SET UserName='$new_username', Email='$new_email', Usermode=-1, BanReason='$escaped_reason', BanExpireTime=-1 WHERE UserId=$puid;")) {
            PostSessionBanner("User account deleted", "green");
            $uid = $user['UserId'];
            $username = $user['DisplayName'];
            $puid = $profile_user['UserId'];
            $pusername = $profile_user['DisplayName'];
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> Deleted user account <strong><a href='/user/$puid/'>$pusername</a></strong>", "");
        } else {
            PostSessionBanner("Failed to delete account", "red");
        }
        break;
    default:
        // Do nothing.
        break;
}
// Go back to requesting page.
Redirect($_SERVER['HTTP_REFERER']);
?>