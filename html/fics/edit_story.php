<?php
// Page for composing new stories and editing existing stories.

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (isset($_POST) && isset($_POST['sid'])) {
    include_once(SITE_ROOT."fics/save_story_changes.php");
    if (isset($success) && $success && isset($sid) && $sid > 0) {
        header("Location: /fics/story/$sid/");
    } else {
        unset($success);
        if (isset($errmsg)) $vars['errmsg'] = $errmsg;
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
    $story = GetStory($sid) or RenderErrorPage("Story not found");;
    if (!CanUserEditStory($story, $user)) RenderErrorPage("Not authorized to edit story");
    $chapters = GetChaptersInfo($sid) or RenderErrorPage("Story not found");
    $vars['story'] = $story;
    $vars['chapters'] = &$chapters;
    // Assign chapter hashes.
    foreach ($chapters as &$chapter) {
        $chapter['hash'] = GetHashForChapter($sid, $chapter['ChapterId']);
    }
} else {
    if (!CanUserCreateStory($user)) RenderErrorPage("Not authorized to create a story");
    $story = array(
        "StoryId" => -1
    );
    $chapter = array();
    $vars['chapter'] = &$chapter;
    $vars['chaptertitle'] = "Chapter 1";
}

if (isset($fill_from_post) && $fill_from_post) {
    // Modify story object to take in errored fields.
    if (isset($title)) $story['Title'] = $title;
    if (isset($summary)) $story['Summary'] = $summary;
    if (isset($rating)) $story['Rating'] = $rating;
    if (isset($completed)) $story['Completed'] = $completed;
    if (isset($storynotes)) $story['StoryNotes'] = $storynotes;
    // Fill chapter fields.
    if (isset($chaptertitle)) $vars['chaptertitle'] = $chaptertitle;
    if (isset($chapternotes)) $vars['chapternotes'] = $chapternotes;
    if (isset($chaptertext)) $vars['chaptertext'] = $chaptertext;
    if (isset($chapterendnotes)) $vars['chapterendnotes'] = $chapterendnotes;
}
$vars['formstory'] = $story;

RenderPage("fics/edit/editstory.tpl");
return;

?>