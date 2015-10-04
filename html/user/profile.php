<?php
// General account profile page.
// URL: /user/{user-id}/
// URL: /user/profile.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."user/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");

$profile_user = &$vars['profile']['user'];
// Read in bio text file.
$uid = $profile_user['UserId'];
$file_path = SITE_ROOT."user/data/bio/$uid.txt";
read_file($file_path, $bio_contents) or RenderErrorPage("Error loading profile.");
$profile_user['bio'] = SanitizeHTMLTags($bio_contents, DEFAULT_ALLOWED_TAGS);
$profile_user['hasBio'] = (mb_strlen($bio_contents) > 0);
$profile_user['admin'] = GetAdminBadge($profile_user);
$profile_user['birthday'] = DateStringToReadableString($profile_user['DOB']);
$profile_user['registerDate'] = FormatDate($profile_user['JoinTime'], PROFILE_DATE_FORMAT);
$profile_user['lastVisitDate'] = FormatDate($profile_user['LastVisitTime'], PROFILE_DATE_TIME_FORMAT);
sql_query_into($result, "SELECT count(*) FROM ".FORUMS_POST_TABLE." WHERE UserId=$uid;", 0) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numForumPosts'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$uid AND Status<>'D';", 0) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numGalleryUploads'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".FICS_STORY_TABLE." WHERE AuthorUserId=$uid AND ApprovalStatus<>'D';", 0) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numFicsStories'] = $result->fetch_assoc()['count(*)'];
// TODO: Fetch oekaki post statistics.
$profile_user['numOekakiDrawn'] = 0;
$profile_user['currentTime'] = FormatDate(time(), PROFILE_TIME_FORMAT, $profile_user['Timezone']);

// Init private visible statistics.
if (isset($user)) {
    $vars['canEditBio'] = CanUserEditBio($user, $profile_user);
    $vars['canEditBasicInfo'] = CanUserEditBasicInfo($user, $profile_user);
    $vars['canSeePrivateInfo'] = CanUserSeePrivateInfo($user, $profile_user);
    $vars['canSeeAdminInfo'] = CanUserSeeAdminInfo($user);
    // Set up known access IPs.
    if (mb_strlen($profile_user['KnownIPs']) == 0) {
        $ips = array();
    } else {
        $ips = explode(",", $profile_user['KnownIPs']);
    }
    if (mb_strlen($profile_user['RegisterIP']) > 0 && mb_strpos($profile_user['KnownIPs'], $profile_user['RegisterIP']) === FALSE) {
        $ips[] = $profile_user['RegisterIP'];
    }
    $profile_user['ips'] = implode(",", $ips);
    // Set up global admin links.
    $admin_links = array();
    if (contains($profile_user['Permissions'], 'A')) {
        if ($user['UserId'] == $profile_user['UserId']) {
            // Don't allow admins to revoke their own admin status by accident.
        } else {
            AddAdminActionLink($admin_links, array("site-A", "forums=N", "gallery=N", "fics=N"), "Revoke Site Administrator");
        }
    } else {
        AddAdminActionLink($admin_links, array("site=A", "forums=A", "gallery=A", "fics=A"), "Make Site Administrator");
        AddAdminActionLinkBreak($admin_links);
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
        AddAdminActionLinkBreak($admin_links);
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
        AddAdminActionLinkBreak($admin_links);
        // Fics options.
        if ($profile_user['FicsPermissions'] == 'R') {
            AddAdminActionLink($admin_links, array("fics=N"), "Unrestrict Fics Edits");
            AddAdminActionLink($admin_links, array("site+F", "fics=A"), "Make Fics Administrator");
        } else if ($profile_user['FicsPermissions'] == 'N') {
            AddAdminActionLink($admin_links, array("fics=R"), "Restrict Fics Edits");
            AddAdminActionLink($admin_links, array("site+F", "fics=A"), "Make Fics Administrator");
        } else if ($profile_user['FicsPermissions'] == 'A') {
            AddAdminActionLink($admin_links, array("site-F", "fics=N"), "Revoke Fics Administrator");
        }
    }
    $vars['adminLinks'] = $admin_links;

    $ban_links = array();
    if (CanUserBan($user, $profile_user)) {
        // Show ban links.
        $is_banned = false;
        if ($profile_user['Usermode'] == -1) {
            // If marked ban has expired, update the database here.
            if ($profile_user['BanExpireTime'] != -1 && time() > $profile_user['BanExpireTime']) {
                $puid = $profile_user['UserId'];
                sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$puid;");
                // Update ban status badge.
                $profile_user['admin'] = GetAdminBadge($profile_user);
                $profile_user['Usermode'] = 1;
            } else {
                // Ban did not expire, user is currently banned.
                $is_banned = true;
            }
        }
        if ($is_banned) {
            $ban_links[] = array(
                "formId" => 0,
                "action" => "unban",
                "duration" => 0,
                "text" => "Unban user",
                "isBan" => false);
        } else {
            $ban_links[] = array(
                "formId" => 0,
                "action" => "tempban",
                "duration" => (int)GetSiteSetting(SHORT_BAN_DURATION_KEY, DEFAULT_SHORT_BAN_DURATION),
                "text" => "Temporarily ban user",
                "isBan" => true);
            $ban_links[] = array(
                "formId" => 1,
                "action" => "permban",
                "duration" => 0,
                "text" => "Permanently ban user",
                "isBan" => true);
        }
    }
    $vars['banLinks'] = $ban_links;
    
    // Get ban status.
    if ($profile_user['Usermode'] == -1) {
        $profile_user['isBanned'] = true;
        if ($profile_user['BanExpireTime'] == -1) {
            $profile_user['banDuration'] = "Permanent";
        } else {
            $profile_user['banDuration'] = FormatDuration($profile_user['BanExpireTime'] - time());
        }
    }
}

$profile_user['hasBasicInfo'] = (
    $profile_user['ShowDOB'] ||
    mb_strlen($profile_user['Species']) ||
    mb_strlen($profile_user['Title']) ||
    mb_strlen($profile_user['Location']) ||
    mb_strlen($profile_user['gender']) ||
    (isset($vars['canSeePrivateInfo']) && $vars['canSeePrivateInfo']));

// This is how to output the template.
RenderPage("user/profile.tpl");
return;

?>