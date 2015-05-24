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

function CanUserEditBasicInfo($user, $profile_user) {
    if (!IsUserActivated($user)) return false;
    if (mb_strpos($user['Permissions'], 'A') !== FALSE) return true;
    if ($user['UserId'] == $profile_user['UserId']) return true;
    return false;
}
?>