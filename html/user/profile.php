<?php
// General account profile page.
// URL: /user/{user-id}/
// URL: /user/profile.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");

$profile_user = &$vars['profile']['user'];
// Read in bio text file.
$uid = $profile_user['UserId'];
$file_path = SITE_ROOT."user/data/bio/$uid.txt";
read_file($file_path, $bio_contents) or RenderErrorPage("Error loading profile.");
$profile_user['bio'] = $bio_contents;
$profile_user['admin'] = GetAdminBadge($profile_user);
$profile_user['birthday'] = DateStringToReadableString($profile_user['DOB']);
// TODO: Show timezone?
$profile_user['registerDate'] = FormatDate($profile_user['JoinTime'], PROFILE_DATE_FORMAT);
$profile_user['lastVisitDate'] = FormatDate($profile_user['LastVisitTime'], PROFILE_DATE_TIME_FORMAT);
sql_query_into($result, "SELECT count(*) FROM ".FORUMS_POST_TABLE." WHERE UserId=$uid;", 0) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numForumPosts'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$uid AND Status<>'D';", 0) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryUploads'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".FICS_STORY_TABLE." WHERE AuthorUserId=$uid AND ApprovalStatus<>'D';", 0) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numFicsStories'] = $result->fetch_assoc()['count(*)'];
// TODO: Oekaki data.
$profile_user['numOekakiDrawn'] = 0;

// Init private visible statistics.
if (isset($user)) {
    $vars['canEditBio'] = CanUserEditBio($user, $profile_user);
    $vars['canEditBasicInfo'] = CanUserEditBasicInfo($user, $profile_user);
    $vars['canSeePrivateInfo'] = CanUserSeePrivateInfo($user, $profile_user);
    $vars['canSeeAdminInfo'] = CanUserSeeAdminInfo($user);
    if (mb_strlen($profile_user['RegisterIP']) == 0 || mb_strpos($profile_user['KnownIPs'], $profile_user['RegisterIP']) !== FALSE) {
        $profile_user['ips'] = $profile_user['KnownIPs'];
    } else {
        $profile_user['ips'] = $profile_user['RegisterIP'].",".$profile_user['KnownIPs'];
    }
    // Set up global admin links.
    $admin_links = array();
    if (contains($profile_user['Permissions'], 'A')) {
        AddAdminActionLink($admin_links, array("site-A", "gallery=N", "fics=N"), "Revoke Site Administrator");
    } else {
        AddAdminActionLink($admin_links, array("site=A", "gallery=A", "fics=A"), "Make Site Administrator");
        // Gallery options.
        if ($profile_user['GalleryPermissions'] == 'R') {
            AddAdminActionLink($admin_links, array("gallery=N"), "Unrestrict Gallery Edits");
            AddAdminActionLink($admin_links, array("gallery=C"), "Make Gallery Contributor");
            AddAdminActionLink($admin_links, array("site+G", "gallery=A"), "Make Gallery Administrator");
        } else if ($profile_user['GalleryPermissions'] == 'N') {
            AddAdminActionLink($admin_links, array("gallery=R"), "Restrict Gallery Edits");
            AddAdminActionLink($admin_links, array("gallery=C"), "Make Gallery Contributor");
            AddAdminActionLink($admin_links, array("site+G", "gallery=A"), "Make Gallery Administrator");
        } else if ($profile_user['GalleryPermissions'] == 'C') {
            AddAdminActionLink($admin_links, array("gallery=N"), "Revoke Gallery Contributor");
            AddAdminActionLink($admin_links, array("site+G", "gallery=A"), "Make Gallery Administrator");
        } else if ($profile_user['GalleryPermissions'] == 'A') {
            AddAdminActionLink($admin_links, array("site-G", "gallery=N"), "Revoke Gallery Administrator");
        }
        // Fics options.
        if ($profile_user['FicsPermissions'] == 'N') {
            AddAdminActionLink($admin_links, array("site+F", "fics=A"), "Make Fics Administrator");
        } else if ($profile_user['FicsPermissions'] == 'A') {
            AddAdminActionLink($admin_links, array("site-F", "fics=N"), "Revoke Fics Administrator");
        }
    }
    $vars['adminLinks'] = $admin_links;
}

// This is how to output the template.
RenderPage("user/profile.tpl");
return;

?>