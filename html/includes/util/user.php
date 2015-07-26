<?php
// Utility functions for user permissions.

include_once(SITE_ROOT."gallery/includes/functions.php");  // To get site thumbnail path.

function IsUserBanned($user) {
    return $user['Usermode'] == -1;
}
function IsUserUnactivated($user) {
    return $user['Usermode'] == 0;
}
function IsUserActivated($user) {
    return $user['Usermode'] == 1;
}
function GetAvatarURL($user) {
    if ($user['AvatarPostId'] != -1) {
        $pid = $user['AvatarPostId'];
        if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId=$pid;", 1)) {
            $post = $result->fetch_assoc();
            if ($post['Status'] != 'D') {
                $md5 = $post['Md5'];
                $ext = $post['Extension'];
                if ($ext == "jpg" || $ext == "png" || $ext == "gif") {
                    // Actually an image, get thumb.
                    // Extension passed in doesn't matter, will end up being GALLERY_THUMB_FILE_EXTENSION.
                    $path = GetSiteThumbPath($md5, $ext);
                    return $path;
                }
            }
        }
    }
    // Not a valid gallery post, check uploaded filename. Always resides inside uploads/
    if (strlen($user['AvatarFname'])) {
        $path = "/images/uploads/avatars/".$user['AvatarFname'];
        return $path;
    }
    return DEFAULT_AVATAR_PATH;
}

function ShouldShowAdminTab($user) {
    // Permissions: A=Super Admin, R=Forums, G=Gallery, F=Fics, O=Oekaki, I=IRC, M=Minecraft
    // Admin tab available to everyone except IRC and Minecraft.
    if (contains($user['Permissions'], 'A')) return true;
    if (contains($user['Permissions'], 'R')) return true;
    if (contains($user['Permissions'], 'G')) return true;
    if (contains($user['Permissions'], 'F')) return true;
    if (contains($user['Permissions'], 'O')) return true;
    return false;
}

?>