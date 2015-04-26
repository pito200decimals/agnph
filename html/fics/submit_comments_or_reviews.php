<?php
// PHP include for handling submissions of fics comments, reviews and author responses.
// Always included in story and chapter pages, but return immediately if not a POST request.
// Included after $story is defined on story page, and after $story and $chapter are defined on chapter page.

include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");

if (!isset($user)) return;
if (!isset($_POST['type'])) return;
$postType = $_POST['type'];
if ($postType == "comment") {
    // Expect textbox "text".
    if (!isset($_POST['text'])) InvalidURL();
    $text = SanitizeHTMLTags($_POST['text'], DEFAULT_ALLOWED_TAGS);
    if (!CanUserComment($user)) RenderErrorPage("You are not authorized to comment");
    $score = 0;  // Comments can't have a score.
    // Also, unset $_GET['reviews'] since we want to redirect to comments.
    unset($_GET['reviews']);
    if (strlen($text) < MIN_COMMENT_STRING_SIZE) RenderErrorPage("Comment length is too short");
} else if ($postType == "review") {
    // Expect textbox "text", select "score".
    if (!isset($_POST['text'])) InvalidURL();
    $text = SanitizeHTMLTags($_POST['text'], DEFAULT_ALLOWED_TAGS);
    if (!isset($_POST['score'])) InvalidURL();
    $score = $_POST['score'];
    if (!is_numeric($score)) InvalidURL();
    if ($score < 0 || $score > 10) InvalidURL();
    if (!CanUserReview($user)) RenderErrorPage("You are not authorized to review");
    if (strlen($text) < MIN_COMMENT_STRING_SIZE) RenderErrorPage("Review length is too short");
    // Also, set $_GET['reviews'] since we want to redirect to reviews.
    $_GET['reviews'] = true;
} else if ($postType == "response") {
    // Expect textbox "text", input "reviewId".
    if (!isset($_POST['text'])) InvalidURL();
    $text = SanitizeHTMLTags($_POST['text'], DEFAULT_ALLOWED_TAGS);
    if (!isset($_POST['reviewId'])) InvalidURL();
    $reviewId = $_POST['reviewId'];
    if (!is_numeric($reviewId)) InvalidURL();
    if (isset($chapter)) {
        if ($user['UserId'] != $chapter['AuthorUserId']) RenderErrorPage("You are not authorized to respond to reviews");
    } else {
        if ($user['UserId'] != $story['AuthorUserId']) RenderErrorPage("You are not authorized to respond to reviews");
    }
    // Also, set $_GET['reviews'] since we want to redirect to reviews.
    $_GET['reviews'] = true;
} else {
    return;
}

// Now that inputs are checked, actually process submission.
$now = time();
if ($postType == "comment" || $postType == "review") {
    $sid = $story['StoryId'];
    if (isset($chapter)) {
        $cid = $chapter['ChapterId'];
    } else {
        $cid = -1;
    }
    $reviewerUserId = $user['UserId'];
    $date = $now;
    $escaped_text = sql_escape($text);
    $escaped_score = sql_escape($score);
    $isReview = ($postType == "review") ? "true" : "false";
    $isComment = ($postType == "comment") ? "true" : "false";
    debug($isReview);
    debug($isComment);
    $success = sql_query("INSERT INTO ".FICS_REVIEW_TABLE."
        (StoryId, ChapterId, ReviewerUserId, ReviewDate, ReviewText, ReviewScore, IsReview, IsComment)
        VALUES
        ($sid, $cid, $reviewerUserId, $now, '$escaped_text', '$escaped_score', $isReview, $isComment);");
    if ($success) {
        if ($postType == "review") {
            // Update stats.
            UpdateStoryStats($story['StoryId']);
            // Increment local vars since we don't want to re-query db.
            if (isset($chapter)) {
                $chapter['NumReviews']++;
                if ($score > 0) {
                    $chapter['TotalStars'] += $score;
                    $chapter['TotalRatings']++;
                }
            }
            $story['NumReviews']++;
            if ($score > 0) {
                $story['TotalStars'] += $score;
                $story['TotalRatings']++;
            }
            $story['stars'] = GetStarsHTML($story['TotalStars'], $story['TotalRatings']);
        }
    } else {
        RenderErrorPage("Unable to perform action");
    }
} else if ($postType == "response") {
    // Make sure review exists.
    $escaped_review_id = sql_escape($reviewId);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_REVIEW_TABLE." WHERE ReviewId='$escaped_review_id';", 1)) RenderErrorPage("Unable to post response 1");
    $review = $result->fetch_assoc();
    if (strlen($review['AuthorResponseText']) > 0) RenderErrorPage("Cannot respond to review twice");
    $escaped_text = sql_escape($text);
    $success = sql_query("UPDATE ".FICS_REVIEW_TABLE." SET AuthorResponseText='$escaped_text' WHERE ReviewId='$escaped_review_id';");
    if (!$success) RenderErrorPage("Unable to post response 2");
}

?>