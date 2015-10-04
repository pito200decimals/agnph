<?php
// Page for processing POSTS for modifying fics user favorites (and author following).

include_once("../header.php");

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
            if (!sql_query_into($result, "SELECT * FROM ".FICS_USER_FAVORITES_TABLE." WHERE UserId=$uid AND StoryId=$sid;", 1)) {
                // Failed to query, or did not find existing favorite.
                if (sql_query("INSERT INTO ".FICS_USER_FAVORITES_TABLE." (StoryId, UserId, Timestamp) VALUES ($sid, $uid, $now);")) {
                    PostSessionBanner("Story added to favorites", "green");
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