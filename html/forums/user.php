<?php
// User forums profile page.
// URL: /user/{user-id}/forums/
// File: /forums/user.php?uid={user-id}

define("PRETTY_PAGE_NAME", "User profile");

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");

$profile_user = &$vars['profile']['user'];
$profile_uid = $profile_user['UserId'];
$profile_user['admin'] = GetAdminBadge($profile_user);

// Don't count coauthors for stories uploaded.
sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE UserId=$profile_uid;", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numForumPosts'] = $result->fetch_assoc()['C'];
sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE UserId=$profile_uid AND IsThread=1;", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numThreadsStarted'] = $result->fetch_assoc()['C'];

$recent = array();
if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE UserId=$profile_uid ORDER BY PostDate DESC LIMIT ".FORUMS_PROFILE_SHOW_NUM_RECENT_POSTS.";", 1)) {
    while ($row = $result->fetch_assoc()) {
        $recent[] = $row;
    }
}
$profile_user['recentPosts'] = $recent;

$admin_links = array();
if (!contains($profile_user['Permissions'], 'A')) {
    // Forums options.
    if ($profile_user['ForumsPermissions'] == 'R') {
        AddAdminActionLink($admin_links, array("forums=N"), "Unrestrict Forums Edits");
        AddAdminActionLink($admin_links, array("site+R", "forums=A"), "Make Forums Administrator");
    } else if ($profile_user['ForumsPermissions'] == 'N') {
        AddAdminActionLink($admin_links, array("forums=R"), "Restrict Forums Edits");
        AddAdminActionLink($admin_links, array("site+R", "forums=A"), "Make Forums Administrator");
    } else if ($profile_user['ForumsPermissions'] == 'A') {
        AddAdminActionLink($admin_links, array("site-R", "forums=N"), "Revoke Forums Administrator");
    }
}
$vars['adminLinks'] = $admin_links;

// This is how to output the template.
RenderPage("user/forums.tpl");
return;
?>