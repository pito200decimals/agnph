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


function DateStringToReadableString($datestr) {
    // $datestr is in the database format "MM/DD/YYYY"
    // TODO: Account properly for time zone conversion. Probably just parse it manually.
    $datetime = strtotime($datestr);
    return FormatDate($datetime, PROFILE_DOB_FORMAT);
}

function CanUserMakeSiteAdmin($user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    return false;
}

function CanUserMakeForumsAdmin($user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if (mb_strpos($user['Permissions'], 'R') !== FALSE) return true;
    return false;
}

function CanUserMakeGalleryAdmin($user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if (mb_strpos($user['Permissions'], 'G') !== FALSE) return true;
    return false;
}

function CanUserMakeFicsAdmin($user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if (mb_strpos($user['Permissions'], 'F') !== FALSE) return true;
    return false;
}

function CanUserMakeOekakiAdmin($user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if (mb_strpos($user['Permissions'], 'O') !== FALSE) return true;
    return false;
}

function GetAdminBadge($profile_user) {
    $ret = array();
    if (mb_strpos($profile_user['Permissions'], 'A') !== FALSE) $ret[] = "Administrator";
    if (mb_strpos($profile_user['Permissions'], 'R') !== FALSE) $ret[] = "Forums Moderator";
    if (mb_strpos($profile_user['Permissions'], 'G') !== FALSE) $ret[] = "Gallery Moderator";
    if (mb_strpos($profile_user['Permissions'], 'F') !== FALSE) $ret[] = "Fics Moderator";
    if (mb_strpos($profile_user['Permissions'], 'O') !== FALSE) $ret[] = "Oekaki Moderator";
    if (mb_strpos($profile_user['Permissions'], 'I') !== FALSE) $ret[] = "IRC Moderator";
    if (mb_strpos($profile_user['Permissions'], 'M') !== FALSE) $ret[] = "Minecraft Moderator";
    if (sizeof($ret) == 0) return "";
    return implode(",", $ret);
}
?>