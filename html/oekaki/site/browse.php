<?php
// Views a page of oekaki posts.
// URL: /oekaki/ => browse.php
// URL: /oekaki/comment/ => browse.php

include_once("../../header.php");
include_once(SITE_ROOT."oekaki/site/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/listview.php");
include_once(SITE_ROOT."includes/util/date.php");

// Handle comment posting.
if (isset($_POST['action'])) {
    if ($_POST['action'] == "comment" &&
        isset($_POST['post-id']) && is_numeric($_POST['post-id']) &&
        isset($_POST['text'])) {
        if (!isset($user)) {
            PostSessionBanner("Must be logged in to comment", "red");
        } else {
            HandleCommentPost();
        }
        Redirect($_SERVER['HTTP_REFERER']);
        return;
    } else if ($_POST['action'] == "delete" &&
               isset($_POST['id']) && is_numeric($_POST['id'])) {
        if (!isset($user)) {
            PostSessionBanner("Must be logged in to delete post", "red");
        } else {
            HandleDeletePost();
        }
        Redirect($_SERVER['HTTP_REFERER']);
        return;
   }
}

$posts_per_page = 25;

$post_sql_condition = "TRUE";
// Check for custom search parameters.
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $lower_search = mb_strtolower($search);
    $escaped_lower_search = sql_escape($lower_search);
    $post_sql_condition =
        "LOWER(Title) LIKE '%$escaped_lower_search%' OR ".
        "LOWER(Text) LIKE '%$escaped_lower_search%' OR ".
        "EXISTS(SELECT 1 FROM ".USER_TABLE." Q WHERE LOWER(DisplayName) LIKE '%$escaped_lower_search%' AND Q.UserId=T.UserId)";
    $vars['searchTerms'] = $search;
}

// Get all parent posts.
$sql_select = "*";
$sql_comments_select = "*";
$sql_order = "WHERE ParentPostId=-1 AND (Status='A' OR Status='M') AND ($post_sql_condition) ORDER BY Timestamp DESC, PostId DESC";
CollectItemsComplex(OEKAKI_POST_TABLE, "SELECT $sql_select FROM ".OEKAKI_POST_TABLE." T", $sql_order, $sql_order, $posts, $posts_per_page, $iterator, "Error displaying oekaki posts");
$posts_by_id = array();
foreach ($posts as &$post) {
    $post['comments'] = array();
    $posts_by_id[$post['PostId']] = &$post;
}
// Get all child comments.
$post_ids_list = "(".implode(",", array_map(function($post) { return $post['PostId']; }, $posts)).")";
// Note: Comments can't have status 'M', only 'A' and 'D'.
$get_comments_sql = "SELECT $sql_comments_select FROM ".OEKAKI_POST_TABLE." WHERE ParentPostId IN $post_ids_list AND Status='A' ORDER BY Timestamp ASC, PostId ASC;";
if (sql_query_into($result, $get_comments_sql, 0)) {
    while ($row = $result->fetch_assoc()) {
        $posts_by_id[$row['PostId']] = $row;
        $posts_by_id[$row['ParentPostId']]['comments'][] = &$posts_by_id[$row['PostId']];
    }
}
// Update users for all posts so far.
$user_ids = array_map(function($item) { return $item['UserId']; }, $posts_by_id);
if (LoadTableData(array(USER_TABLE), "UserId", $user_ids, $users_by_id)) {
    foreach ($users_by_id as &$usr) {
        $usr['avatarURL'] = GetAvatarURL($usr);
    }
    foreach ($posts_by_id as &$post) {
        $post['user'] = &$users_by_id[$post['UserId']];
    }
}

// Sanitize all post text, add timestamp strings.
foreach ($posts_by_id as &$post) {
    $post['text'] = SanitizeHTMLTags($post['Text'], DEFAULT_ALLOWED_TAGS);
    // TODO: Add potential post actions.
    $post['id'] = $post['PostId'];
    $post['date'] = FormatDate($post['Timestamp']);
    $post['editDate'] = null;
    // Only set escaped title on parent post, not on comments.
    if ($post['ParentPostId'] == -1) {
        $post['escapedTitle'] = SanitizeHTMLTags($post['Title'], NO_HTML_TAGS);
    }
    $post['duration'] = FormatVeryShortDuration($post['Duration']);
    $post['actions'] = array();
    if (CanUserDeletePost($user, $post)) {
        $is_root_post = ($post['ParentPostId'] == -1);
        if ($is_root_post) {
            $msg = "Are you sure you want to delete this image post? All child comments will also be deleted.";
            $label = "Delete Image";
        } else {
            $msg = "Are you sure you want to delete this comment?";
            $label = "Delete Comment";
        }
        $post['actions'][] = array(
            "url" => "/oekaki/comment/",
            "method" => "POST",
            "action" => "delete",
            "id" => $post['PostId'],
            "label" => $label,
            "confirmMsg" => $msg);
    }
}

$vars['posts'] = $posts;
$vars['iterator'] = $iterator;
$vars['url'] = $_SERVER['REQUEST_URI'];

RenderPage("oekaki/browse.tpl", false /* tidy */);
return;

function HandleCommentPost() {
    global $user;
    if (!is_numeric($_POST['post-id'])) return PostSessionBanner("Invalid action", "red");
    $post_id = (int)$_POST['post-id'];
    $escaped_post_id = sql_escape($post_id);
    if (!sql_query_into($result, "SELECT * FROM ".OEKAKI_POST_TABLE." WHERE PostId=$escaped_post_id;", 1)) return PostSessionBanner("Invalid action", "red");
    $post = $result->fetch_assoc();
    if (!CanUserCreateComment($user)) return PostSessionBanner("Unable to comment on post", "red");
    $post_id = $post['PostId'];  // Re-fetch post id.
    $text = $_POST['text'];
    $text_only = SanitizeHTMLTags($text, NO_HTML_TAGS);
    if (mb_strlen($text_only) < MIN_COMMENT_STRING_SIZE) return PostSessionBanner("Comment does not meet minimum length requirements", "red");
    $text = SanitizeHTMLTags($text, DEFAULT_ALLOWED_TAGS);
    $escaped_text = sql_escape($text);
    $uid = $user['UserId'];
    $timestamp = time();
    if (!sql_query("INSERT INTO ".OEKAKI_POST_TABLE." (UserId, ParentPostId, Timestamp, Text) VALUES ($uid, $post_id, $timestamp, '$escaped_text');")) return PostSessionBanner("Error processing comment", "red");
    PostSessionBanner("Comment posted", "green");
}

function HandleDeletePost() {
    global $user;
    if (!is_numeric($_POST['id'])) return PostSessionBanner("Invalid action", "red");
    $post_id = (int)$_POST['id'];
    $escaped_post_id = sql_escape($post_id);
    if (!sql_query_into($result, "SELECT * FROM ".OEKAKI_POST_TABLE." WHERE PostId=$escaped_post_id;", 1)) return PostSessionBanner("Invalid action", "red");
    $post = $result->fetch_assoc();
    if (!CanUserDeletePost($user, $post)) return PostSessionBanner("Not authorized to delete post", "red");
    $post_id = $post['PostId'];  // Re-fetch post id.
    $is_root_post = ($post['ParentPostId'] == -1);
    if ($is_root_post) {
        $parent_id = $post['PostId'];
    } else {
        $parent_id = $post['ParentPostId'];
    }
    $escaped_post_id = sql_escape($post_id);
    if (!sql_query("UPDATE ".OEKAKI_POST_TABLE." SET Status='D' WHERE PostId='$escaped_post_id' OR ParentPostId='$escaped_post_id';")) return PostSessionBanner("Failed to delete post", "red");

    // Do logging if a root post was deleted.
    if ($is_root_post) {
        $uid = $user['UserId'];
        $pid = $post['PostId'];
        $username = $user['DisplayName'];
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> deleted post #$pid", "O");
    }
    PostSessionBanner("Post deleted", "green");
}
?>