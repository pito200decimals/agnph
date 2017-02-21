<?php
// User oekaki profile page.
// URL: /user/{user-id}/oekaki/
// File: /oekaki/site/user.php?uid={user-id}

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/date.php");
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

// Fetch some links to posts.
if (sql_query_into($result, "SELECT * FROM ".OEKAKI_POST_TABLE." WHERE UserId=$profile_uid AND ParentPostId=-1 AND Status='A' ORDER BY Timestamp DESC LIMIT ".OEKAKI_PROFILE_SHOW_NUM_POSTS.";", 1)) {
    $sample_posts = array();
    while ($row = $result->fetch_assoc()) {
        $row['thumbnail'] = "/oekaki/image/".$row['PostId'].".".$row['Extension'];
        $sample_posts[] = $row;
    }
    $profile_user['sample_posts'] = $sample_posts;
}

$slots = array();
$uid = $profile_user['UserId'];
if (isset($user) && $profile_user['UserId'] == $user['UserId']) {
    for ($i = 0; $i < MAX_OEKAKI_SAVE_SLOTS; $i++) {
        $metadata = GetValidMetadataOrNull($i);
        if ($metadata != null && file_exists(SITE_ROOT."user/data/oekaki/$uid/slot$i/".OEKAKI_THUMB_FILE_NAME)) {
            $formatted_duration = FormatVeryShortDuration($metadata['elapsedSeconds']);
            $slot = array(
                "thumb" => "/oekaki/thumb/$i.png",
                "href" => "/oekaki/draw/#".($i + 1),
                "duration" => $formatted_duration,
                "name" => $metadata['name'],
                );
            $slots[] = $slot;
        }
    }
}
$vars['slots'] = $slots;

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