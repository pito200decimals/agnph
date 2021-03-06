<?php
// PHP page for receiving actions to change administrator permission status. Also handles user bans.
// URL: /user/{user-id}/admin/
// URL: /user/change_admin.php?uid={user-id}

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

$actions = $_POST['action'];
$success = false;
// Parse action string.
// TODO: Add oekaki permissions when section is finished.
$ACTION_TABLE = array(
    "site" => USER_TABLE,
    "forums" => FORUMS_USER_PREF_TABLE,
    "gallery" => GALLERY_USER_PREF_TABLE,
    "fics" => FICS_USER_PREF_TABLE,
    "oekaki" => OEKAKI_USER_PREF_TABLE);
$ACTION_KEYS = array(
    "site" => "Permissions",
    "forums" => "ForumsPermissions",
    "gallery" => "GalleryPermissions",
    "fics" => "FicsPermissions",
    "oekaki" => "OekakiPermissions");
$ALLOWED_CHARS = array(
    "site" => "ARGFOIM",
    "forums" => "ANR",
    "gallery" => "ACNR",
    "fics" => "ANR",
    "oekaki" => "ANR");
foreach ($actions as $action) {
    foreach ($ACTION_TABLE as $prefix => $table) {
        if (startsWith($action, $prefix)) {
            $perm = substr($action, strlen($prefix));  // Okay to use non-mb here.
            if (!CanUserSetPermissions($user, $profile_user, $prefix, $perm)) {
                RenderErrorPage("Insufficient permissions");
                return;
            }
            $value = substr($perm, 1);
            if (!contains($ALLOWED_CHARS[$prefix], $value)) {
                RenderErrorPage("Invalid permission action: ".$action);
                return;
            }
            $key = $ACTION_KEYS[$prefix];
            if ($perm[0] == '+' && $prefix == "site") {
                $new_perms = str_replace($value, "", $profile_user[$key]) . $value;
            } else if ($perm[0] == '-' && $prefix == "site") {
                $new_perms = str_replace($value, "", $profile_user[$key]);
            } else if ($perm[0] == '=') {
                $new_perms = $value;
            } else {
                RenderErrorPage("Invalid permission action: ".$action);
                return;
            }
            if (isset($new_perms)) {
                $escaped_perms = sql_escape($new_perms);  // Okay to not sanitize this.
                $success = sql_query("UPDATE $table SET $key='$escaped_perms' WHERE UserId=$puid;");
            }
        }
    }
}

if ($success) {
    PostSessionBanner("Permissions changed", "green");
    $action_str = implode(",", $actions);
    $uid = $user['UserId'];
    $username = $user['DisplayName'];
    $puid = $profile_user['UserId'];
    $pusername = $profile_user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> changed privileges of user <strong><a href='/user/$puid/'>$pusername</a></strong> ($action_str)", "");
}
// Go back to requesting page.
Redirect($_SERVER['HTTP_REFERER']);
?>