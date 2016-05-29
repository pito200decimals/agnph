<?php
// General functions for the oekaki section.

include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");

function CanUserCreatePost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['OekakiPermissions'] == 'A') return true;
    // Restrict user based on permissions.
    if ($user['OekakiPermissions'] == 'R') return false;
    return true;
}

function CanUserCreateComment($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['OekakiPermissions'] == 'A') return true;
    // Restrict user based on permissions.
    if ($user['OekakiPermissions'] == 'R') return false;
    return true;
}

function CanUserDeletePost($user, $post) {
    if (!IsUserActivated($user)) return false;
    if ($user['OekakiPermissions'] == 'A') return true;
    // Restrict user based on permissions. Restricted users cannot delete their own comments/posts.
    if ($user['OekakiPermissions'] == 'R') return false;
    if ($user['UserId'] == $post['UserId']) return true;  // Creator can delete.
    return false;
}

?>