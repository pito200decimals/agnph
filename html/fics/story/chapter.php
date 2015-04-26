<?php
// Page for displaying a chapter.
// URL: /fics/story/{story-id}/{chapter-num}/
// URL: /fics/story/story.php?sid={story-id}&chapter={chapter-num}

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) InvalidURL();
$sid = $_GET['sid'];  // Avoid user-inputted story id.
if (!isset($_GET['chapter']) || !is_numeric($_GET['chapter'])) InvalidURL();
$chapternum = $_GET['chapter'];

$story = GetStory($sid) or RenderErrorPage("Story not found");
$chapters = GetChaptersInfo($sid);
if ($chapternum <= 0 || $chapternum > sizeof($chapters)) RenderErrorPage("Chapter not found");
$chapter = $chapters[$chapternum - 1];
$chapter['text'] = GetChapterText($chapter['ChapterId']) or RenderErrorPage("Chapter not found");

include_once(SITE_ROOT."FICS/submit_comments_or_reviews.php");

// Get chapter author(s).
$chapter_author_ids = array($chapter['AuthorUserId']);
$authors = array();
if (!LoadTableData(array(USER_TABLE), "UserId", $chapter_author_ids, $authors)) RenderErrorPage("Chapter not found");
$chapter['author'] = $authors[$chapter['AuthorUserId']];

$vars['story'] = $story;
$vars['chapter'] = $chapter;
$vars['numchapters'] = sizeof($chapters);

// Also fetch comments/reviews.
$chapterReviews = GetReviews($sid);
$vars['reviews'] = array_filter($chapterReviews, function($review) use ($chapter) {
    return $review['IsReview'] && $review['ChapterId'] == $chapter['ChapterId'];
});
$vars['comments'] = array_filter($chapterReviews, function($review) use ($chapter) {
    return $review['IsComment'] && $review['ChapterId'] == $chapter['ChapterId'];
});

if (isset($_GET['reviews'])) $vars['defaultreviews'] = true;
else $vars['defaultcomments'] = true;

if (isset($user) && CanUserComment($user)) {
    $vars['canComment'] = true;
}
if (isset($user) && CanUserReview($user)) {
    $vars['canReview'] = true;
}

// TODO: Comment/reviews pagination

// Increment view count.
$cid = $chapter['ChapterId'];
sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET Views=Views+1 WHERE ChapterId=$cid;");
sql_query("UPDATE ".FICS_STORY_TABLE." SET Views=Views+1 WHERE StoryId=$sid;");

RenderPage("fics/story/chapter.tpl");
return;
?>