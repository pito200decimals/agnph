<?php
// Page for composing or editing chapters.

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (isset($_POST) && isset($_POST['sid']) && isset($_POST['chapternum'])) {
    include_once(SITE_ROOT."fics/save_chapter_changes.php");
    if (isset($success) && $success && isset($sid) && $sid > 0) {
        // TODO: Go to story edit page?
        header("Location: /fics/edit/$sid/");
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

if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) RenderErrorPage("Invalid URL");
$sid = $_GET['sid'];
$story = GetStory($sid);
if ($story == null) {
    RenderErrorPage("Story not found");
}
$vars['storyid'] = $story['StoryId'];
$chapters = GetChaptersInfo($sid);

if ($action == "create") {
    // No $cid.
    $vars['create'] = true;
    $chapternum = sizeof($chapters) + 1;
} else if ($action == "edit") {
    if (!isset($_GET['chapternum']) || !is_numeric($_GET['chapternum'])) RenderErrorPage("Invalid URL");
    $chapternum = $_GET['chapternum'];
    if ($chapternum <= 0 || $chapternum > sizeof($chapters)) RenderErrorPage("Chapter not found.");
    $chapter = $chapters[$chapternum - 1];
    $vars['chaptertitle'] = $chapter['Title'];
    $vars['chapternotes'] = $chapter['ChapterNotes'];
    $cid = $chapter['ChapterId'];
    $chaptertext = GetChapterText($cid);
    if ($chaptertext == null) {
        RenderErrorPage("Chapter not found.");
    } else {
        $vars['chaptertext'] = $chaptertext;
    }
    $vars['chapterendnotes'] = $chapter['ChapterEndNotes'];
    $vars['chapterid'] = GetHashForChapter($sid, $cid);
    $vars['edit'] = true;
} else {
    RenderErrorPage("Invalid URL");
}
$vars['chapternum'] = $chapternum;

if (!isset($user)) {
    if ($action == "create") {
        RenderErrorPage("Must be logged in to create stories.");
    } else {
        RenderErrorPage("Must be logged in to edit stories.");
    }
}

if (isset($fill_from_post) && $fill_from_post) {
    // Modify story object to take in errored fields.
    if (isset($chaptertitle)) $vars['chaptertitle'] = $chaptertitle;
    if (isset($chapternotes)) $vars['chapternotes'] = $chapternotes;
    if (isset($chaptertext)) $vars['chaptertext'] = $chaptertext;
    if (isset($chapterendnotes)) $vars['chapterendnotes'] = $chapterendnotes;
}

$vars['backlink'] = "/fics/edit/$sid/";


RenderPage("fics/edit/editchapter.tpl");
return;

?>