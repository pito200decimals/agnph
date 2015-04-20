<?php
// Include page for processing AJAX chapter reorder requests.
// POST request includes sid, original order num, chapter hash, new order num.
// If new index is < old index, move chapters towards end until desired index is reached.
// If new index is > old index, move chapters towards front until desired index is reached.

define("DEBUG", false);
include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (!isset($user)) {
    AJAXErr();
}
CheckNumericAndSave('sid', $sid);
CheckNumericAndSave('oldnum', $oldchapternum);
CheckNumericAndSave('newnum', $newchapternum);
if (!isset($_POST['id'])) AJAXErr();
$chapterid = $_POST['id'];

$story = GetStory($sid) or AJAXErr();
if (!CanUserEditStory($story, $user)) AJAXErr();
$sid = $story['StoryId'];  // Get good id, avoid user-supplied id.
$chapters = GetChaptersInfo($sid);

// Check indices in-bounds.
if ($oldchapternum <= 0 || $oldchapternum > sizeof($chapters)) AJAXErr();
if ($newchapternum <= 0 || $newchapternum > sizeof($chapters)) AJAXErr();
// Check hash is correct.
$oldchapter = $chapters[$oldchapternum - 1];
$old_cid = $oldchapter['ChapterId'];
$old_hash = GetHashForChapter($sid, $old_cid);
debug($chapterid);
debug($old_hash);
debug("OldCid: $old_cid");
if ($chapterid != $old_hash) InvalidURL();

if ($newchapternum > $oldchapternum) {
    for ($i = $oldchapternum - 1; $i < $newchapternum - 1; $i++) {
        $oldchapter = $chapters[$i];
        $nextchapter = $chapters[$i + 1];
        $chapters[$i] = $nextchapter;
        $chapters[$i + 1] = $oldchapter;
    }
} else if ($newchapternum < $oldchapternum) {
    for ($i = $oldchapternum - 1; $i > $newchapternum - 1; $i--) {
        $oldchapter = $chapters[$i];
        $prevchapter = $chapters[$i - 1];
        $chapters[$i] = $prevchapter;
        $chapters[$i - 1] = $oldchapter;
    }
} else {
    // Nothing to change!
    return;
}
// Get new indices, and SQL change the differences.
for ($i = 0; $i < sizeof($chapters); $i++) {
    if ($chapters[$i]['ChapterItemOrder'] != $i) {
        // At this point, we can't really recover well from failures.
        $cid = $chapters[$i]['ChapterId'];
        sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET ChapterItemOrder=$i WHERE ChapterId=$cid;");
    }
}
return;

function CheckNumericAndSave($id, &$val) {
    if (!isset($_POST[$id]) || !is_numeric($_POST[$id])) AJAXErr();
    $val = $_POST[$id];
}
?>