<?php
// Page for composing new stories and editing existing stories.

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (!CanPerformSitePost()) MaintenanceError();

if (isset($_POST) && isset($_POST['sid'])) {
    include_once(SITE_ROOT."fics/save_story_changes.php");
    if (isset($success) && $success && isset($sid) && $sid > 0) {
        header("Location: /fics/story/$sid/");
        exit();
    } else {
        unset($success);
        if (isset($errmsg)) PostBanner($errmsg, "red");
        // Failed, but try to repopulate story fields.
        $fill_from_post = true;
    }
}

if (!isset($_GET['action'])) InvalidURL();
$action = $_GET['action'];
$vars['action'] = $action;

if ($action == "create") {
    // No $sid.
    $vars['create'] = true;
} else if ($action == "edit") {
    if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) InvalidURL();
    $sid = $_GET['sid'];
    $vars['edit'] = true;
} else {
    InvalidURL();
}
if (!isset($user)) {
    if ($action == "create") {
        RenderErrorPage("Must be logged in to create stories");
    } else {
        RenderErrorPage("Must be logged in to edit stories");
    }
}

if ($action == "edit") {
    // Update story stats before fetching story data.
    // Avoids issues with chapter re-ordering and such, in case the database
    // gets into some sort of corrupted state.
    UpdateStoryStats($sid);
    $story = GetStory($sid) or RenderErrorPage("Story not found");
    if ($story['ApprovalStatus'] == 'D') {
        RenderErrorPage("Story not found");
        return;
    }
    if (!CanUserEditStory($story, $user)) RenderErrorPage("Not authorized to edit story");
    FillStoryInfo($story);
    $chapters = GetChaptersInfo($sid) or RenderErrorPage("Story not found");
    $story['tagstring'] = implode(" ", array_map(function($tag) { return $tag['Name']; }, $story['tags']));
    $vars['story'] = $story;  // For story block.
    $vars['chapters'] = &$chapters;
    // Assign chapter hashes.
    foreach ($chapters as &$chapter) {
        $chapter['hash'] = GetHashForChapter($sid, $chapter['ChapterId']);
    }
} else {
    if (!CanUserCreateStory($user)) RenderErrorPage("Not authorized to create a story");
    $story = array(
        "StoryId" => -1,
        "author" => $user,
        "coauthors" => array(),
        "AuthorUserId" => $user['UserId']
    );
    $chapter = array();
    $vars['chapter'] = &$chapter;
    $vars['chaptertitle'] = "Chapter 1";
}

if (isset($fill_from_post) && $fill_from_post) {
    // Modify story object to take in errored fields.
    if (isset($title)) $story['Title'] = $title;
    if (isset($author_uid)) {
        $story['AuthorUserId'] = $author_uid;
        LoadSingleTableEntry(array(USER_TABLE), "UserId", $author_uid, $story['author']);
    }
    if (isset($coauthor_ids)) {
        $story['CoAuthors'] = implode(",", $coauthor_ids);
        LoadTableData(array(USER_TABLE), "UserId", $coauthor_ids, $story['coauthors']);
    }
    if (isset($summary)) $story['Summary'] = $summary;
    if (isset($rating)) $story['Rating'] = $rating;
    if (isset($completed)) $story['Completed'] = $completed;
    if (isset($storynotes)) $story['StoryNotes'] = $storynotes;
    if (isset($tagstring)) $story['tagstring'] = $tagstring;
    // Fill chapter fields.
    if (isset($chaptertitle)) $vars['chaptertitle'] = $chaptertitle;
    if (isset($chapternotes)) $vars['chapternotes'] = $chapternotes;
    if (isset($chaptertext)) $vars['chaptertext'] = $chaptertext;
    if (isset($chapterendnotes)) $vars['chapterendnotes'] = $chapterendnotes;
}
$vars['formstory'] = $story;
$vars['canSetAuthor'] = CanUserChooseAnyAuthor($user);
$vars['canSetCoAuthors'] = CanUserSetCoAuthors($story, $user);

RenderPage("fics/edit/editstory.tpl");
return;

?>