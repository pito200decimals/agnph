<?php
// Page for displaying a story summary and TOC.
// URL: /fics/story/{story-id}/
// URL: /fics/story/story.php?sid={story-id}\

define("PRETTY_PAGE_NAME", "Fics");

include_once("../../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");

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
$vars['_title'] = $story['Title']." by ".$story['author']['DisplayName']." - AGNPH - Fics";

$storyReviews = GetReviews($sid);
if ($storyReviews == null) $storyReviews = array();
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
$includeAllCommentsOnStory = TRUE;  // TODO: Get from user settings.
$comments = array_filter($storyReviews, function($review) use ($includeAllCommentsOnStory) {
    return $review['IsComment'] && ($includeAllCommentsOnStory || $review['ChapterId'] == -1);
});
$reviews = array_filter($storyReviews, function($review) {
    // Get reviews for story as well as all chapters.
    return $review['IsReview'];
});
ConstructCommentBlockIterator($comments, $vars['commentIterator'], !isset($_GET['reviews']),
    function($index) use ($sid) {
        $offset = ($index - 1) * FICS_COMMENTS_PER_PAGE;
        $url = "/fics/story/$sid/?offset=$offset";
        return $url;
    }, FICS_COMMENTS_PER_PAGE);
ConstructCommentBlockIterator($reviews, $vars['reviewIterator'], isset($_GET['reviews']),
    function($index) use ($sid) {
        $offset = ($index - 1) * FICS_COMMENTS_PER_PAGE;
        $url = "/fics/story/$sid/?reviews&offset=$offset#reviews";
        return $url;
    }, FICS_COMMENTS_PER_PAGE);

// Format comments for template.
$comments = array_map(function($comment) use ($includeAllCommentsOnStory) {
        global $user;
        return array(
            'id' => $comment['id'],
            'user' => $comment['commenter'],
            'date' => $comment['date'],
            'title' => ($includeAllCommentsOnStory ? $comment['chapterTitle'] : ""),
            'text' => $comment['ReviewText'],
            'actions' => $comment['actions']);
    }, $comments);
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