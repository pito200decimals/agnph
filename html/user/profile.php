<?php
// General account profile page.
// URL: /user/{user-id}/
// URL: /user/profile.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");

include_once(SITE_ROOT."user/includes/profile_setup.php");

// Read in bio text file.
$uid = $profile_user['UserId'];
$file_path = SITE_ROOT."user/data/bio/$uid.txt";
read_file($file_path, $bio_contents) or RenderErrorPage("Error loading profile.");
$profile_user = &$vars['profile']['user'];
$profile_user['bio'] = $bio_contents;
$profile_user['admin'] = GetAdminBadge($profile_user);
$profile_user['birthday'] = DateToString($profile_user['DOB']);
$profile_user['registerDate'] = FormatDate($profile_user['JoinTime'], PROFILE_DATE_FORMAT);
$profile_user['lastVisitDate'] = FormatDate($profile_user['LastVisitTime'], PROFILE_DATE_TIME_FORMAT);
if (sql_query_into($result, "SELECT count(*) FROM ".FORUMS_POST_TABLE." WHERE UserId=$uid;", 0)) {
    $profile_user['numForumPosts'] = $result->fetch_assoc()['count(*)'];
} else {
    $profile_user['numForumPosts'] = 0;
}
if (sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$uid;", 0)) {
    $profile_user['numGalleryUploads'] = $result->fetch_assoc()['count(*)'];
} else {
    $profile_user['numGalleryUploads'] = 0;
}
if (sql_query_into($result, "SELECT count(*) FROM ".FICS_STORY_TABLE." WHERE AuthorUserId=$uid;", 0)) {
    $profile_user['numFicsStories'] = $result->fetch_assoc()['count(*)'];
} else {
    $profile_user['numFicsStories'] = 0;
}
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
}

// This is how to output the template.
RenderPage("user/profile.tpl");
return;

function DateToString($datestr) {
    // TODO: Account properly for time zone conversion. Probably just parse it manually.
    $datetime = strtotime($datestr);
    return FormatDate($datetime, PROFILE_DOB_FORMAT);
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