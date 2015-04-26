<?php
// Page for displaying a story summary and TOC.
// URL: /fics/story/{story-id}/
// URL: /fics/story/story.php?sid={story-id}

include_once("../../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/file.php");

if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) InvalidURL();

$sid = $_GET['sid'];
$story = GetStory($sid) or RenderErrorPage("Story not found");

include_once(SITE_ROOT."fics/submit_comments_or_reviews.php");

$chapters = GetChaptersInfo($sid) or RenderErrorPage("Story not found");
$vars['story'] = &$story;
$vars['chapters'] = &$chapters;

$storyReviews = GetReviews($sid);
// Get chapter titles for reviews/comments.
foreach ($storyReviews as &$review) {
    $title = "";
    if ($review['ChapterId'] != -1) {
        foreach ($chapters as $chapter) {
            if ($chapter['ChapterId'] == $review['ChapterId']) {
                $title = $chapter['Title'];
            }
        }
    }
    $review['chapterTitle'] = $title;
}
$vars['reviews'] = array_filter($storyReviews, function($review) {
    // Get reviews for story as well as all chapters.
    return $review['IsReview'];
});
$vars['comments'] = array_filter($storyReviews, function($review) {
    return $review['IsComment'] && $review['ChapterId'] == -1;
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

RenderPage("fics/story/story.tpl");
return;
?>