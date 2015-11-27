<?php
// PHP include for handling submissions of fics comments, reviews and author responses.
// Always included in story and chapter pages, but return immediately if not a POST request.
// Included after $story is defined on story page, and after $story and $chapter are defined on chapter page.

include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");

if (!isset($user)) return;
if (!CanPerformSitePost()) MaintenanceError();
if (!isset($_POST['action'])) return;
if ($story['ApprovalStatus'] == "D") RenderErrorPage("Story not found.");
$action = $_POST['action'];
if ($action == "comment") {
    // Expect textbox "text".
    if (!isset($_POST['text'])) InvalidURL();
    $text = GetSanitizedTextTruncated($_POST['text'], DEFAULT_ALLOWED_TAGS, MAX_FICS_COMMENT_LENGTH);
    if (!CanUserComment($user)) RenderErrorPage("You are not authorized to comment");
    $score = 0;  // Comments can't have a score.
    // Also, unset $_GET['reviews'] since we want to redirect to comments.
    unset($_GET['reviews']);
    if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) RenderErrorPage("Comment length is too short");
} else if ($action == "review") {
    // Expect textbox "text", select "score".
    if (!isset($_POST['text'])) InvalidURL();
    $text = GetSanitizedTextTruncated($_POST['text'], DEFAULT_ALLOWED_TAGS, MAX_FICS_COMMENT_LENGTH);
    if (!isset($_POST['score'])) InvalidURL();
    $score = $_POST['score'];
    if (!is_numeric($score)) InvalidURL();
    if ($score < 0 || $score > 10) InvalidURL();
    if (!CanUserReview($user)) RenderErrorPage("You are not authorized to review");
    if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) RenderErrorPage("Review length is too short");
    // Also, set $_GET['reviews'] since we want to redirect to reviews.
    $_GET['reviews'] = true;
} else if ($action == "response") {
    // Expect textbox "text", input "reviewId".
    if (!isset($_POST['text'])) InvalidURL();
    $text = GetSanitizedTextTruncated($_POST['text'], DEFAULT_ALLOWED_TAGS, MAX_FICS_COMMENT_LENGTH);
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
} else if ($action == "delete-comment") {
    // Expect id
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) InvalidURL();
    $cid = (int)$_POST['id'];
    $escaped_cid = sql_escape($cid);
    sql_query_into($result, "SELECT * FROM ".FICS_REVIEW_TABLE." WHERE ReviewId=$escaped_cid;", 1) or RenderErrorPage("Comment cannot be deleted");
    $comment = $result->fetch_assoc();
    if (!CanUserDeleteComment($user, $comment)) RenderErrorPage("Permission denied");
} else {
    return;
}

// Now that inputs are checked, actually process submission.
$now = time();
if ($action == "comment" || $action == "review") {
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
    $isReview = ($action == "review") ? "true" : "false";
    $isComment = ($action == "comment") ? "true" : "false";
    $success = sql_query("INSERT INTO ".FICS_REVIEW_TABLE."
        (StoryId, ChapterId, ReviewerUserId, ReviewDate, ReviewText, ReviewScore, IsReview, IsComment)
        VALUES
        ($sid, $cid, $reviewerUserId, $now, '$escaped_text', '$escaped_score', $isReview, $isComment);");
    if ($success) {
        if ($action == "review") {
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
            $story['stars'] = GetStars($story['TotalStars'], $story['TotalRatings']);
            PostSessionBanner("Review posted", "green");
        } else {
            PostSessionBanner("Comment posted", "green");
        }
        // Go back to requesting page.
        Redirect($_SERVER['HTTP_REFERER']);
    } else {
        RenderErrorPage("Unable to perform action");
    }
} else if ($action == "response") {
    // Make sure review exists.
    $escaped_review_id = sql_escape($reviewId);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_REVIEW_TABLE." WHERE ReviewId='$escaped_review_id';", 1)) RenderErrorPage("Error while posting response");
    $review = $result->fetch_assoc();
    if (mb_strlen($review['AuthorResponseText']) > 0) RenderErrorPage("Cannot respond to review twice");
    $escaped_text = sql_escape($text);
    $success = sql_query("UPDATE ".FICS_REVIEW_TABLE." SET AuthorResponseText='$escaped_text' WHERE ReviewId='$escaped_review_id';");
    if ($success){
        PostSessionBanner("Response posted", "green");
        // Go back to requesting page.
        Redirect($_SERVER['HTTP_REFERER']);
    } else {
        RenderErrorPage("Error while posting response");
    }
} else if ($action == "delete-comment" && isset($comment)) {
    $cid = $comment['ReviewId'];  // Get database value.
    sql_query("DELETE FROM ".FICS_REVIEW_TABLE." WHERE ReviewId=$cid;");
    UpdateStoryStats($story['StoryId']);
    if ($comment['IsReview']) {
        PostSessionBanner("Review deleted", "green");
    } else {
        PostSessionBanner("Comment deleted", "green");
    }
    // Go back to requesting page.
    Redirect($_SERVER['HTTP_REFERER']);
}

?>