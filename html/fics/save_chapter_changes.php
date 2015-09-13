<?php
// Include page for processing chapter changes.

include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."includes/util/file.php");

if (!isset($user)) {
    return;
}
if (!CanPerformSitePost()) MaintenanceError();
if (!isset($_POST['sid'])) return;
$sid = $_POST['sid'];
if (!isset($_POST['chapternum'])) return;
$chapternum = $_POST['chapternum'];
if (!isset($_POST['chaptertitle'])) return;
$chaptertitle = SanitizeHTMLTags($_POST['chaptertitle'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['chapternotes'])) return;
$chapternotes = SanitizeHTMLTags($_POST['chapternotes'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['chaptertext'])) return;
$chaptertext = SanitizeHTMLTags($_POST['chaptertext'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['chapterendnotes'])) return;
$chapterendnotes = SanitizeHTMLTags($_POST['chapterendnotes'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['chapterid'])) return;
$chapterid = $_POST['chapterid'];

if (!isset($_GET['action'])) return;
$action = $_GET['action'];

// Check for valid input.
if (!is_numeric($sid)) return;
if (mb_strlen($chaptertitle) == 0) {
    $errmsg = "Invalid Chapter Title";
    return;
}
// Check min word count.
$min_word_count = GetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, DEFAULT_FICS_CHAPTER_MIN_WORD_COUNT);
if ($min_word_count > 0) {
    $word_count = ChapterWordCount($chaptertext);
    if ($word_count < $min_word_count) {
        $errmsg = "Chapter must be at least $min_word_count words long.";
        return;
    }
}

// Edit existing story.
$story = GetStory($sid);
if ($story == null) {
    $errmsg = "Story not found";
    return;
}
if (!CanUserEditStory($story, $user)) RenderErrorPage("Not authorized to edit story");
$sid = $story['StoryId'];  // Get proper $sid, in case of user mangling.

// Get chapters by index.
$chapters = GetChaptersInfo($sid);
if ($chapters == null) {
    $errmsg = "Story not found";
    return;
}
if ($action == "edit") {
    if ($chapternum <= 0 || $chapternum > sizeof($chapters)) {
        $errmsg = "Chapter not found";
        return;
    }
    $chapter = $chapters[$chapternum - 1];
    $cid = $chapter['ChapterId'];
    if ($chapterid != GetHashForChapter($sid, $cid)) {
        $errmsg = "Chapter not found";
        return;
    }

    // We got the right chapter to update. Only update the changed sections.

    $sets = array();
    if ($chaptertitle != $chapter['Title']) {
        $escaped_title = sql_escape($chaptertitle);
        $sets[] = "Title='$escaped_title'";
    }
    if ($chapternotes != $chapter['ChapterNotes']) {
        $escaped_notes = sql_escape($chapternotes);
        $sets[] = "ChapterNotes='$escaped_notes'";
    }
    if ($chapterendnotes != $chapter['ChapterEndNotes']) {
        $escaped_endnotes = sql_escape($chapterendnotes);
        $sets[] = "ChapterEndNotes='$escaped_endnotes'";
    }
    if (sizeof($sets) > 0) {
        // Don't update DateUpdated. Don't want to bump stories.
        $sets = implode(",", $sets);
        $success = sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET $sets WHERE ChapterId=$cid;");
        if (!$success) return;
    } else {
        // Nothing to change.
        $success = true;
    }

    // If chapter text changed, update that (only if previous steps succeeded).
    $oldchaptertext = GetChapterText($cid);
    if ($oldchaptertext == null) {
        $success = false;
        return;
    }
    $success = SetChapterText($cid, $chaptertext);
    
    // Update story word count. Just do a full count, hopefully it's not too expensive.
    UpdateStoryStats($sid);
    return;
} else if ($action == "create") {
    // Create new chapter.
    if ($chapternum != sizeof($chapters) + 1) InvalidURL();
    $escaped_title = sql_escape($chaptertitle);
    $escaped_notes = sql_escape($chapternotes);
    $escaped_endnotes = sql_escape($chapterendnotes);
    $uid = $user['UserId'];
    $chapterindex = $chapternum - 1;
    $success = sql_query("INSERT INTO ".FICS_CHAPTER_TABLE."
        (ParentStoryId, AuthorUserId, Title, ChapterItemOrder, ChapterNotes, ChapterEndNotes)
        VALUES
        ($sid, $uid, '$escaped_title', $chapterindex, '$escaped_notes', '$escaped_endnotes');");
    if (!$success) return;
    
    $cid = sql_last_id();
    $success = SetChapterText($cid, $chaptertext);
    if (!$success) {
        // Delete story entry.
        sql_query("DELETE FROM ".FICS_CHAPTER_TABLE." WHERE ChapterId=$cid;");
        return;
    }
    
    // Update story word count. Just do a full count, hopefully it's not too expensive.
    UpdateStoryStats($sid);
    return;
} else {
    return;
}
?>