<?php
// Include page for processing story changes.

include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."includes/util/file.php");

if (!isset($user)) {
    return;
}

if (!isset($_POST['sid'])) return;
$sid = $_POST['sid'];
if (!isset($_POST['title'])) return;
$title = $_POST['title'];
if (!isset($_POST['summary'])) return;
$summary = $_POST['summary'];
if (!isset($_POST['rating'])) return;
$rating = $_POST['rating'];
if (!isset($_POST['completed'])) return;
$completed = $_POST['completed'];
if (!isset($_POST['notes'])) return;
$storynotes = $_POST['notes'];

// Check for valid input.
if (!is_numeric($sid)) return;
if (strlen($title) == 0) {
    $errmsg = "Invalid Story Title";
}
if ($rating == '1') {
    $rating = 'G';
} else if ($rating == '2') {
    $rating = 'P';
} else if ($rating == '3') {
    $rating = 'T';
} else if ($rating == '4') {
    $rating = 'R';
} else if ($rating == '5') {
    $rating = 'X';
} else {
    return;
}
if ($completed == '1') {
    $completed = false;
} else if ($completed == '2') {
    $completed = true;
} else {
    return;
}

// Check chapter 1 input, if applicable.
if ($sid <= 0) {
    if (!isset($_POST['chaptertitle'])) return;
    $chaptertitle = $_POST['chaptertitle'];
    if (!isset($_POST['chapternotes'])) return;
    $chapternotes = $_POST['chapternotes'];
    if (!isset($_POST['chaptertext'])) return;
    $chaptertext = SanitizeHTMLTags($_POST['chaptertext'], DEFAULT_ALLOWED_TAGS);
    if (!isset($_POST['chapterendnotes'])) return;
    $chapterendnotes = $_POST['chapterendnotes'];
    if (strlen($chaptertitle) == 0) {
        $errmsg = "Invalid Chapter Title";
    }
}

if (isset($errmsg) && strlen($errmsg) > 0) return;

if ($sid > 0) {
    // Edit existing story.
    $story = GetStory($sid);
    if ($story == null) return;
    if (!CanUserEditStory($story, $user)) RenderErrorPage("Not authorized to edit story");
    $sid = $story['StoryId'];
    $sets = array();
    if ($title != $story['Title']) {
        $escaped_title = sql_escape($title);
        $sets[] = "Title='$escaped_title'";
    }
    if ($summary != $story['Summary']) {
        $escaped_summary = sql_escape($summary);
        $sets[] = "Summary='$escaped_summary'";
    }
    if ($rating != $story['Rating']) {
        $sets[] = "Rating='$rating'";
    }
    if ($completed != $story['Completed']) {
        $sets[] = "Completed=$completed";
    }
    if ($storynotes != $story['StoryNotes']) {
        $escaped_story_notes = sql_escape($storynotes);
        $sets[] = "StoryNotes='$escaped_story_notes'";
    }
    if (sizeof($sets) > 0) {
        // Don't update DateUpdated. Don't want to bump stories.
        $sets = implode(",", $sets);
        $success = sql_query("UPDATE ".FICS_STORY_TABLE." SET $sets WHERE StoryId=$sid;");
    } else {
        // Nothing to change.
        $success = true;
    }
    return;
} else {
    if (!CanUserCreateStory($user)) RenderErrorPage("Not authorized to create a story");
    // Create new story.
    $escaped_title = sql_escape($title);
    $escaped_summary = sql_escape($summary);
    $escaped_story_notes = sql_escape($storynotes);
    $chapter_count = 1;
    $word_count = ChapterWordCount($chaptertext);
    $uid = $user['UserId'];
    $now = time();
    if ($completed) $completed = "true";
    else $completed = "false";
    $success = sql_query("INSERT INTO ".FICS_STORY_TABLE."
        (AuthorUserId, DateCreated, DateUpdated, Title, Summary, Rating, Completed, ChapterCount, WordCount)
        VALUES
        ($uid, $now, $now, '$escaped_title', '$escaped_summary', '$rating', $completed, $chapter_count, $word_count);");
    if (!$success) return;
    $sid = sql_last_id();
    $escaped_chap_title = sql_escape($chaptertitle);
    $escaped_chap_notes = sql_escape($chapternotes);
    $escaped_chap_endnotes = sql_escape($chapterendnotes);
    $success = sql_query("INSERT INTO ".FICS_CHAPTER_TABLE."
        (ParentStoryId, AuthorUserId, Title, ChapterItemOrder, ChapterNotes, ChapterEndNotes)
        VALUES
        ($sid, $uid, '$escaped_chap_title', 0, '$escaped_chap_notes', '$escaped_chap_endnotes');");
    if (!$success) {
        // Delete story.
        sql_query("DELETE FROM ".FICS_STORY_TABLE." WHERE StoryId=$sid;");
        return;
    }
    $cid = sql_last_id();
    // Write chapter contents to file.
    $success = SetChapterText($cid, $chaptertext);
    if (!$success) {
        // Delete story.
        sql_query("DELETE FROM ".FICS_CHAPTER_TABLE." WHERE StoryId=$cid;");
        sql_query("DELETE FROM ".FICS_STORY_TABLE." WHERE StoryId=$sid;");
        return;
    }
}
?>