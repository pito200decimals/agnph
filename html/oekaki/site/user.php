<?php
// User oekaki profile page.
// URL: /user/{user-id}/oekaki/
// File: /oekaki/site/user.php?uid={user-id}

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."oekaki/site/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");

$profile_user = &$vars['profile']['user'];
$profile_uid = $profile_user['UserId'];
$profile_user['admin'] = GetAdminBadge($profile_user);

// Fetch user statistics.
// Posts Uploaded by user and not deleted:
sql_query_into($result, "SELECT count(*) FROM ".OEKAKI_POST_TABLE." WHERE UserId=$profile_uid AND ParentPostId=-1 AND Status='A';", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numOekakiImagePosts'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".OEKAKI_POST_TABLE." WHERE UserId=$profile_uid AND ParentPostId<>-1 AND Status='A';", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numComments'] = $result->fetch_assoc()['count(*)'];

// TODO: Add section showing some sample image posts for the user.
// Fetch some links to posts.
//if (sql_query_into($result, "SELECT * FROM ".OEKAKI_POST_TABLE." WHERE UserId=$profile_uid AND ParentPostId=-1 AND Status='A' ORDER Timestamp DESC LIMIT 5;", 1)) {
//    $sample_posts = array();
//    while ($row = $result->fetch_assoc()) {
//        $sample_posts[] = $row;
//    }
//    $profile_user['sample_posts'] = $sample_posts;
//}

// TODO: Add section for pending images.

// Set up global admin links.
$admin_links = array();
if (!contains($profile_user['Permissions'], 'A')) {
    // Oekaki options.
    if ($profile_user['OekakiPermissions'] == 'R') {
        AddAdminActionLink($admin_links, array("gallery=N"), "Unrestrict Oekaki Edits");
        AddAdminActionLink($admin_links, array("site+O", "oekaki=A"), "Make Oekaki Administrator");
    } else if ($profile_user['OekakiPermissions'] == 'N') {
        AddAdminActionLink($admin_links, array("oekaki=R"), "Restrict Oekaki Edits");
        AddAdminActionLink($admin_links, array("site+O", "oekaki=A"), "Make Oekaki Administrator");
    } else if ($profile_user['OekakiPermissions'] == 'A') {
        AddAdminActionLink($admin_links, array("site-O", "oekaki=N"), "Revoke Oekaki Administrator");
    }
}
$vars['adminLinks'] = $admin_links;

// This is how to output the template.
RenderPage("user/oekaki.tpl");
return;
?>