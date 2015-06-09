<?php
// Page for composing or editing chapters.

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
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

if (!isset($_GET['action'])) InvalidURL();
$action = $_GET['action'];
$vars['action'] = $action;

if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) InvalidURL();
$sid = $_GET['sid'];
$story = GetStory($sid) or RenderErrorPage("Story not found");
if ($story['ApprovalStatus'] == 'D') {
    RenderErrorPage("Story not found");
    return;
}
$vars['storyid'] = $story['StoryId'];
$chapters = GetChaptersInfo($sid);

if ($action == "create") {
    // No $cid.
    $vars['create'] = true;
    $chapternum = sizeof($chapters) + 1;
} else if ($action == "edit") {
    if (!isset($_GET['chapternum']) || !is_numeric($_GET['chapternum'])) InvalidURL();
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
    InvalidURL();
}
$vars['chapternum'] = $chapternum;

if (!isset($user)) {
    RenderErrorPage("Must be logged in to edit stories");
} else if (!CanUserEditStory($story, $user)) {
    RenderErrorPage("Not authroized to edit story");
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