<?php
// Page for composing new stories and editing existing stories.

include_once("../header.php");
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

if (!isset($_GET['action'])) RenderErrorPage("Invalid URL");
$action = $_GET['action'];
$vars['action'] = $action;

if ($action == "create") {
    // No $sid.
    $vars['create'] = true;
} else if ($action == "edit") {
    if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) RenderErrorPage("Invalid URL");
    $sid = $_GET['sid'];
    $vars['edit'] = true;
} else {
    RenderErrorPage("Invalid URL");
}
if (!isset($user)) {
    if ($action == "create") {
        RenderErrorPage("Must be logged in to create stories.");
    } else {
        RenderErrorPage("Must be logged in to edit stories.");
    }
}

if ($action == "edit") {
    $story = GetStory($sid) or RenderErrorPage("Story not found.");;
    if (!CanUserEditStory($story, $user)) RenderErrorPage("Not authorized to edit story.");
    $chapters = GetChaptersInfo($sid) or RenderErrorPage("Story not found.");
    $vars['story'] = $story;
    $vars['chapters'] = &$chapters;
} else {
    $story = array(
        "StoryId" => -1
    );
    $chapter = array();
    $vars['chapter'] = &$chapter;
}

if (isset($fill_from_post) && $fill_from_post) {
    // Modify story object to take in errored fields.
    if (isset($title)) $story['Title'] = $title;
    if (isset($summary)) $story['Summary'] = $summary;
    if (isset($rating)) $story['Rating'] = $rating;
    if (isset($completed)) $story['Completed'] = $completed;
    if (isset($storynotes)) $story['StoryNotes'] = $storynotes;
    // TODO: Fill chapter fields.
    if (isset($chaptertitle)) $chapter['title'] = $chaptertitle;
    if (isset($chapternotes)) $chapter['notes'] = $chapternotes;
    if (isset($chaptertext)) $chapter['text'] = $chaptertext;
    if (isset($chapterendnotes)) $chapter['endnotes'] = $chapterendnotes;
}
$vars['formstory'] = $story;

RenderPage("fics/edit/editstory.tpl");
return;

?>