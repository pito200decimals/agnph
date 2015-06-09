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
if ($story['ApprovalStatus'] == 'D') {
    RenderErrorPage("Story not found");
    return;
}

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
$comments = array_filter($storyReviews, function($review) {
    return $review['IsComment'] && $review['ChapterId'] == -1;
});
$reviews = array_filter($storyReviews, function($review) {
    // Get reviews for story as well as all chapters.
    return $review['IsReview'];
});
ConstructCommentBlockIterator($comments, $vars['commentIterator'], !isset($_GET['reviews']),
    function($index) use ($sid) {
        $offset = ($index - 1) * DEFAULT_FICS_COMMENTS_PER_PAGE;
        $url = "/fics/story/$sid/?offset=$offset";
        return $url;
    }, DEFAULT_FICS_COMMENTS_PER_PAGE);
ConstructCommentBlockIterator($reviews, $vars['reviewIterator'], isset($_GET['reviews']),
    function($index) use ($sid) {
        $offset = ($index - 1) * DEFAULT_FICS_COMMENTS_PER_PAGE;
        $url = "/fics/story/$sid/?reviews&offset=$offset#reviews";
        return $url;
    }, DEFAULT_FICS_COMMENTS_PER_PAGE);
$vars['comments'] = $comments;
$vars['reviews'] = $reviews;

if (isset($_GET['reviews'])) $vars['defaultreviews'] = true;
else $vars['defaultcomments'] = true;

if (isset($user) && CanUserComment($user)) {
    $vars['canComment'] = true;
}
if (isset($user) && CanUserReview($user)) {
    $vars['canReview'] = true;
}

RenderPage("fics/story/story.tpl");
return;


?>