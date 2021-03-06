<?php
// Page for confirming story deletion.
// URL: /fics/delete/{story-id}/
// URL: /fics/delete/{story-id}/{chapter-index}/
// URL: /fics/undelete/{story-id}/
// URL: /fics/delete_story.php

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (!CanPerformSitePost()) MaintenanceError();

if (!isset($user)) {
    RenderErrorPage("Not authorized to modify story");
    return;
}

if (!isset($_GET['action']) || !isset($_GET['type'])) {
    InvalidURL();
    return;
}
if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) {
    InvalidURL();
    return;
}
$sid = (int)$_GET['sid'];
$story = GetStory($sid);
if ($story == null) {
    InvalidURL();
    return;
}
$sid = $story['StoryId'];  // Get db value.
$vars['story'] = $story;
if (isset($_POST['confirm'])) {
    // Submit action.
    if ($_GET['action'] == "delete" && CanUserDeleteStory($story, $user)) {
        if ($_GET['type'] == "chapter") {
            if (isset($_POST['id']) && isset($_POST['index']) && is_numeric($_POST['index'])) {
                $chapter_index = (int)$_POST['index'];
                $chapter_offset = $chapter_index - 1;
                if (sql_query_into($result, "SELECT * FROM ".FICS_CHAPTER_TABLE." WHERE ParentStoryId=$sid AND ApprovalStatus='A' ORDER BY ChapterItemOrder LIMIT 1 OFFSET $chapter_offset;", 1)) {
                    $chapter = $result->fetch_assoc();
                    $cid = $chapter['ChapterId'];
                    $db_hash = GetHashForChapter($sid, $cid);
                    if ($db_hash == $_POST['id']) {
                        sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET ApprovalStatus='D' WHERE ParentStoryId=$sid AND ChapterId=$cid;");
                        UpdateStoryStats($sid);  // Update views, reviews, word count, chapter order.
                        $uid = $user['UserId'];
                        $username = $user['DisplayName'];
                        $chapterTitle = htmlspecialchars($chapter['Title']);
                        $storyTitle = htmlspecialchars($story['Title']);
                        PostSessionBanner("Chapter deleted", "green");
                        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> deleted chapter <strong>$chapterTitle</strong> ($cid) from story <strong><a href='/fics/story/$sid/'>$storyTitle</a></strong>", "F");
                        Redirect("/fics/edit/$sid/");
                    } else {
                        RenderErrorPage("Unable to delete chapter");
                    }
                } else {
                    RenderErrorPage("Unable to delete chapter");
                }
            } else {
                InvalidURL();
            }
        } else {
            sql_query("UPDATE ".FICS_STORY_TABLE." SET ApprovalStatus='D' WHERE StoryId=$sid;");
            // Delete favorites for this story.
            sql_query("DELETE FROM ".FICS_USER_FAVORITES_TABLE." WHERE StoryId=$sid;");
            $uid = $user['UserId'];
            $username = $user['DisplayName'];
            $storyTitle = htmlspecialchars($story['Title']);
            PostSessionBanner("Story deleted", "green");
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> deleted story <strong><a href='/fics/story/$sid/'>$storyTitle</a></strong> (<a href='/fics/undelete/$sid/'>Undelete</a>)", "F");
            Redirect("/fics/browse/");
        }
    } else if ($_GET['action'] == "undelete" && CanUserUndeleteStory($story, $user)) {
        sql_query("UPDATE ".FICS_STORY_TABLE." SET ApprovalStatus='A' WHERE StoryId=$sid;");
        $uid = $user['UserId'];
        $username = $user['DisplayName'];
        $storyTitle = htmlspecialchars($story['Title']);
        PostSessionBanner("Story un-deleted", "green");
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> un-deleted story <strong><a href='/fics/story/$sid/'>$storyTitle</a></strong>", "F");
        Redirect("/fics/story/$sid/");
    } else {
        RenderErrorPage("Not authorized to modify story");
    }
} else {
    // Render confirm page.
    if ($_GET['action'] == "delete" && CanUserDeleteStory($story, $user)) {
        if ($_GET['type'] == "chapter" && isset($_GET['index']) && is_numeric($_GET['index'])) {
            // Get chapter data and name.
            $chapter_index = (int)$_GET['index'];
            $chapter_offset = $chapter_index - 1;
            if (sql_query_into($result, "SELECT * FROM ".FICS_CHAPTER_TABLE." WHERE ParentStoryId=$sid AND ApprovalStatus='A' ORDER BY ChapterItemOrder LIMIT 1 OFFSET $chapter_offset;", 1)) {
                $chapter = $result->fetch_assoc();
                $cid = $chapter['ChapterId'];
                $vars['chapterHash'] = GetHashForChapter($sid, $cid);
                $vars['chapterIndex'] = $chapter_index;
                $vars['chapterId'] = $cid;
                $vars['actionName'] = "delete chapter '".$chapter['Title']."' from story '".$story['Title']."'";
                $vars['buttonText'] = "Delete Chapter";
            } else {
                RenderErrorPage("Chapter not found");
            }
        } else {
            $vars['actionName'] = "delete this story";
            $vars['buttonText'] = "Delete Story";
        }
    } else if ($_GET['action'] == "undelete" && CanUserUndeleteStory($story, $user)) {
            $vars['actionName'] = "un-delete this story";
            $vars['buttonText'] = "Un-Delete Story";
    } else {
        RenderErrorPage("Not authorized to modify story");
    }
}

RenderPage("fics/deletestory.tpl");
return;

?>