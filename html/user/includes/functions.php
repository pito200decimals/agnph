<?php
// Basic functions for account section.

include_once(SITE_ROOT."includes/util/user.php");

function CanUserSeeAdminInfo($user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    return false;
}
function CanUserSeePrivateInfo($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
function CanUserEditBio($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
function CanUserEditSignature($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
function CanUserEditBasicInfo($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
function CanUserViewPMs($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    // TODO: Do we want to allow admins to view messages?
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
function CanUserSendPMsForUser($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    // Only users can send messages as themselves (no admin spoofing).
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
function CanUserSetPermissions($user, $profile, $action, $perm) {
    // When action = "site", perm = "+/-{char}"
    // When action != "site", perm = "={char}"
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($action == "site") {
        if (mb_strpos($user['Permissions'], $perm[1]) !== FALSE) return true;
    }
    if ($action == "gallery" && mb_strpos($user['Permissions'], "G") !== FALSE) return true;
    if ($action == "fics" && mb_strpos($user['Permissions'], "F") !== FALSE) return true;
    // Default permission for all other users.
    return false;
}
function CanUserQuickChangeName($user, $profile) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    return false;
}
function CanUserChangeEmailAndPasswordWithoutVerification($user, $profile) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    return false;
}
function CanUserBan($user, $profile) {
    if (!IsUserActivated($user)) return false;
    if ($user['UserId'] == $profile['UserId']) return false;  // Don't allow banning self.
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    return false;
}


function DateStringToReadableString($datestr) {
    // $datestr is in the database format "MM/DD/YYYY"
    // TODO: Account properly for time zone conversion. Probably just parse it manually.
    $datetime = strtotime($datestr);
    return FormatDate($datetime, PROFILE_DOB_FORMAT);
}

// TODO: Better badge icon.
function GetAdminBadge($profile_user) {
    if ($profile_user['Usermode'] == -1) {
        return "Banned";
    } else if ($profile_user['Usermode'] == 0) {
        // Unactivated account.
        return "";
    }
    $ret = array();
    $AddBadge = function($name) use (&$ret) {
        $ret[] = array(
            "name" => $name,
            "class" => "badge"
        );
    };
    $AddBadgeImage = function($src) use (&$ret) {
        $ret[] = array(
            "src" => $src,
            "class" => "badge"
        );
    };
    if (mb_strpos($profile_user['Permissions'], 'A') !== FALSE) $AddBadgeImage("/images/site_admin.gif");
    if (mb_strpos($profile_user['Permissions'], 'R') !== FALSE) $AddBadge("Forums Moderator");
    if (mb_strpos($profile_user['Permissions'], 'G') !== FALSE) $AddBadgeImage("/images/gallery_admin.gif");
    if (mb_strpos($profile_user['Permissions'], 'F') !== FALSE) $AddBadgeImage("/images/fics_admin.gif");
    if (mb_strpos($profile_user['Permissions'], 'O') !== FALSE) $AddBadge("Oekaki Administrator");
    if (mb_strpos($profile_user['Permissions'], 'I') !== FALSE) $AddBadgeImage("/images/irc_admin.gif");
    if (mb_strpos($profile_user['Permissions'], 'M') !== FALSE) $AddBadge("Minecraft Moderator");
    if (isset($profile_user['GalleryPermissions']) && mb_strpos($profile_user['GalleryPermissions'], 'C') !== FALSE) $AddBadge("Gallery Contributor");
    if (startsWith($profile_user['UserName'], IMPORTED_ACCOUNT_USERNAME_PREFIX)) $AddBadge("Inactive User");
    if (sizeof($ret) == 0) $AddBadge("User");
    return $ret;
}
// TODO: Actually use this in comments, reviews, forum/oekaki posts and PMs.
function GetPostVisibleBadge($profile_user) {
    $badges = array_filter(GetAdminBadge($profile_user), function($badge) {
        return isset($badge['src']);
    });
    if (sizeof($badges) == 0) return null;
    else return reset($badges);
}

// Actions is a list of actions, each a string of the form "site+A", "gallery=C", etc.
function AddAdminActionLink(&$admin_links, $actions, $text) {
    global $user, $profile_user;
    $link = array();
    $link['formId'] = "admin-link-".sizeof($admin_links);
    foreach ($actions as $action) {
        // Okay to use non-multibyte here.
        $pieces = preg_split("/[+-=]/", $action);
        $key = $pieces[0];
        $value = $pieces[1];
        $op = substr($action, strlen($key), 1);
        if (!CanUserSetPermissions($user, $profile_user, $key, $op.$value)) {
            return;  // Don't add the link.
        }
    }
    $link['actions'] = $actions;
    $link['text'] = $text;
    $admin_links[] = $link;
}
function AddAdminActionLinkBreak(&$admin_links) {
    $admin_links[] = "break";
}
?>