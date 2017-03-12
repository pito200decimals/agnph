<?php
// User fics profile page.
// URL: /user/{user-id}/fics/
// File: /fics/user.php?uid={user-id}

define("PRETTY_PAGE_NAME", "User profile");

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."fics/includes/functions.php");

include(SITE_ROOT."user/includes/profile_setup.php");

$profile_user = &$vars['profile']['user'];
$profile_uid = $profile_user['UserId'];
$profile_user['admin'] = GetAdminBadge($profile_user);

// Don't count coauthors for stories uploaded.
sql_query_into($result, "SELECT count(*) FROM ".FICS_STORY_TABLE." WHERE AuthorUserId=$profile_uid AND ApprovalStatus='A';", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numStoriesUploaded'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".FICS_REVIEW_TABLE." R WHERE
    ReviewerUserId=$profile_uid AND
    IsReview=1 AND
    EXISTS(SELECT 1 FROM ".FICS_CHAPTER_TABLE." C WHERE
            R.ChapterId=C.ChapterId AND
            C.ApprovalStatus='A');", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numReviewsPosted'] = $result->fetch_assoc()['count(*)'];
sql_query_into($result, "SELECT count(*) FROM ".FICS_USER_FAVORITES_TABLE." F WHERE UserId=$profile_uid;", 1) or RenderErrorPage("Failed to fetch user profile");
$profile_user['numFavorites'] = $result->fetch_assoc()['count(*)'];

// Get some recent stories.
$stories = array();
if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE (AuthorUserId=$profile_uid OR INSTR(CONCAT(',', CoAuthors, ','), ',$profile_uid,') <> 0) AND ApprovalStatus='A' ORDER BY DateUpdated DESC, DateCreated DESC, StoryId DESC LIMIT ".FICS_PROFILE_SHOW_NUM_STORIES.";", 1)) {
    while ($story = $result->fetch_assoc()) {
        FillStoryInfo($story);
        $story['shortDesc'] = true;
        $stories[] = $story;
    }
}
$profile_user['stories'] = $stories;

// Get some favorited stories. Deleted stories can't be favorites, so don't worry about status.
$stories = array();
if (sql_query_into($result,
    "SELECT * FROM ".FICS_STORY_TABLE." T WHERE
    EXISTS(SELECT 1 FROM ".FICS_USER_FAVORITES_TABLE." F WHERE T.StoryId=F.StoryId AND F.UserId=$profile_uid)
    ORDER BY T.DateUpdated DESC, DateCreated DESC, StoryId DESC LIMIT ".FICS_PROFILE_SHOW_NUM_FAVORITES.";", 1)) {
    while ($story = $result->fetch_assoc()) {
        FillStoryInfo($story);
        $story['shortDesc'] = true;
        $stories[] = $story;
    }
}
$profile_user['favorites'] = $stories;
$vars['showFavorites'] = ($profile_user['PrivateFicsFavorites'] != 1 || (isset($user) && $profile_user['UserId'] == $user['UserId']));

// Set up global admin links.
$admin_links = array();
if (!contains($profile_user['Permissions'], 'A')) {
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

// This is how to output the template.
RenderPage("user/fics.tpl");
return;
?>