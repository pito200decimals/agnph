<?php
// Page for processing POSTS for modifying fics user favorites (and author following).

include_once("../header.php");
include_once(SITE_ROOT."includes/util/notification.php");

if (!isset($user) || !isset($_POST) || !isset($_POST['action'])) {
    RenderErrorPage("Invalid action");
    return;
}
if (!CanPerformSitePost()) MaintenanceError();
$now = time();
$uid = $user['UserId'];
$action = $_POST['action'];
if ($action == "add-favorite") {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $sid = (int)$_POST['id'];
        $escaped_sid = sql_escape($sid);
        if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE StoryId='$escaped_sid';", 1)) {
            $story = $result->fetch_assoc();
            $sid = $story['StoryId'];
            if ($story['ApprovalStatus'] == "D") {
                PostSessionBanner("Story not found", "green");
                    // Go back to requesting page.
                    Redirect($_SERVER['HTTP_REFERER']);
            }
            if (!sql_query_into($result, "SELECT * FROM ".FICS_USER_FAVORITES_TABLE." WHERE UserId=$uid AND StoryId=$sid;", 1)) {
                // Failed to query, or did not find existing favorite.
                if (sql_query("INSERT INTO ".FICS_USER_FAVORITES_TABLE." (StoryId, UserId, Timestamp) VALUES ($sid, $uid, $now);")) {
                    PostSessionBanner("Story added to favorites", "green");
                    $user_name = $user['DisplayName'];
                    $uid = $user['UserId'];
                    $user_url = SITE_DOMAIN."/user/$uid/";
                    $story_title = $story['Title'];
                    $story_url = SITE_DOMAIN."/fics/story/$sid/";
                    AddNotification(
                        /*user_id=*/$story['AuthorUserId'],
                        /*title=*/"User Favorite",
                        /*contents=*/"<a href='$user_url'>$user_name</a> added <a href='$story_url'>$story_title</a> to their favorites.",
                        /*sender_id=*/$uid);
                    // Go back to requesting page.
                    Redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }
} else if ($action == "remove-favorite") {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $sid = (int)$_POST['id'];
        $escaped_sid = sql_escape($sid);
        if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE StoryId='$escaped_sid';", 1)) {
            $story = $result->fetch_assoc();
            $sid = $story['StoryId'];
            if ($story['ApprovalStatus'] == "D") {
                PostSessionBanner("Story not found", "green");
                    // Go back to requesting page.
                    Redirect($_SERVER['HTTP_REFERER']);
            }
            if (sql_query_into($result, "SELECT * FROM ".FICS_USER_FAVORITES_TABLE." WHERE UserId=$uid AND StoryId=$sid;", 1)) {
                // Found the entry for favorites.
                if (sql_query("DELETE FROM ".FICS_USER_FAVORITES_TABLE." WHERE StoryId=$sid AND UserId=$uid;")) {
                    PostSessionBanner("Story removed from favorites", "green");
                    // Go back to requesting page.
                    Redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }
}

RenderErrorPage("Invalid action");
return;

?>