<?php
// User gallery profile page.
// URL: /user/{user-id}/gallery/
// File: /gallery/user.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");

$profile_user = &$vars['profile']['user'];
$profile_uid = $profile_user['UserId'];
$profile_user['admin'] = GetAdminBadge($profile_user);

// Fetch user statistics.
// Posts Uploaded by user and not deleted:
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$profile_uid AND (Status='P' OR Status='A' OR Status='F');", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryPostsUploaded'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$profile_uid AND (Status='P');", 1) or RenderErrorPage("Failed to fetch user profile");
$num_pending = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$profile_uid AND (Status='F');", 1) or RenderErrorPage("Failed to fetch user profile");
$num_flagged = $result->fetch_assoc()['count(*)'];
$detail_upload_str = array();
if ($num_pending > 0) $detail_upload_str[] = "$num_pending pending";
if ($num_flagged > 0) $detail_upload_str[] = "$num_flagged flagged";
$detail_upload_str = implode(", ", $detail_upload_str);
if (mb_strlen($detail_upload_str) > 0) {
    $profile_user['galleryPostsUploadedDetail'] = "($detail_upload_str)";
} else {
    $profile_user['galleryPostsUploadedDetail'] = "$detail_upload_str";
}
//Posts Flagged by user:
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE FlaggerUserId=$profile_uid AND (Status='F' OR Status='D');", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryPostsFlagged'] = $result->fetch_assoc()['count(*)'];
//Posts Favorited:
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE UserId=$profile_uid;", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryPostsFavorited'] = $result->fetch_assoc()['count(*)'];
//Tag Edits:
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TAG_HISTORY_TABLE." WHERE UserId=$profile_uid;", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryTagEdits'] = $result->fetch_assoc()['count(*)'];
//Post Comments:
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_COMMENT_TABLE." WHERE UserId=$profile_uid;", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryPostComments'] = $result->fetch_assoc()['count(*)'];

if (FetchUploadCountsByUserBySuccess($profile_user['UserId'], $numPending, $numUploaded, $numDeleted)) {
    $limit = ComputeUploadLimit($numUploaded, $numDeleted);
    $profile_user['numBaseUploadLimit'] = 10;
    $profile_user['numGoodUploads'] = $numUploaded;
    $profile_user['numBadUploads'] = $numDeleted;
    $profile_user['numUploadLimit'] = $limit;
} else {
    $profile_user['numBaseUploadLimit'] = 0;
    $profile_user['numGoodUploads'] = 0;
    $profile_user['numBadUploads'] = 0;
    $profile_user['numUploadLimit'] = 0;
}

// Get some recent uploaded posts. Include flagged posts.
$posts = array();
if (sql_query_into($result,
    "SELECT * FROM ".GALLERY_POST_TABLE."
    WHERE UploaderId=$profile_uid AND (Status='P' OR Status='A' OR Status='F')
    ORDER BY DateUploaded DESC, PostId DESC LIMIT ".GALLERY_PROFILE_SHOW_NUM_UPLOADS.";", 0)) {
    while ($row = $result->fetch_assoc()) {
        debug($row);
        $md5 = $row['Md5'];
        $ext = $row['Extension'];
        $row['thumbnail'] = GetSiteThumbPath($md5, $ext);
        CreatePostLabel($row);
        $posts[] = $row;
    }
    SetOutlineClasses($posts);
}
$profile_user['uploads'] = $posts;

// Get some favorited posts. Deleted posts can't be favorites, so don't worry about status.

$posts = array();
if (sql_query_into($result,
    "SELECT * FROM ".GALLERY_POST_TABLE." P JOIN ".GALLERY_USER_FAVORITES_TABLE." F ON P.PostId=F.PostId
    WHERE F.UserId=$profile_uid
    ORDER BY P.DateUploaded DESC, P.PostId DESC LIMIT ".GALLERY_PROFILE_SHOW_NUM_FAVORITES.";", 0)) {
    while ($row = $result->fetch_assoc()) {
        debug($row);
        $md5 = $row['Md5'];
        $ext = $row['Extension'];
        $row['thumbnail'] = GetSiteThumbPath($md5, $ext);
        CreatePostLabel($row);
        $posts[] = $row;
    }
    SetOutlineClasses($posts);
}
$profile_user['favorites'] = $posts;
$vars['showFavorites'] = ($profile_user['PrivateGalleryFavorites'] != 1 || (isset($user) && $profile_user['UserId'] == $user['UserId']));

// Set up global admin links.
$admin_links = array();
if (!contains($profile_user['Permissions'], 'A')) {
    // Gallery options.
    if ($profile_user['GalleryPermissions'] == 'N') {
        AddAdminActionLink($admin_links, array("gallery=C"), "Make Gallery Contributor");
        AddAdminActionLink($admin_links, array("site+G", "gallery=A"), "Make Gallery Administrator");
    } else if ($profile_user['GalleryPermissions'] == 'C') {
        AddAdminActionLink($admin_links, array("gallery=N"), "Revoke Gallery Contributor");
        AddAdminActionLink($admin_links, array("site+G", "gallery=A"), "Make Gallery Administrator");
    } else if ($profile_user['GalleryPermissions'] == 'A') {
        AddAdminActionLink($admin_links, array("site-G", "gallery=N"), "Revoke Gallery Administrator");
    }
}
$vars['adminLinks'] = $admin_links;

// This is how to output the template.
RenderPage("user/gallery.tpl");
return;
?>