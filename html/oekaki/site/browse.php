<?php
// Views a page of oekaki posts.
// URL: /oekaki/ => browse.php
// URL: /oekaki/comment/ => browse.php

define("PRETTY_PAGE_NAME", "Oekaki");

include_once("../../header.php");
include_once(SITE_ROOT."oekaki/site/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/listview.php");
include_once(SITE_ROOT."includes/util/date.php");
include_once(SITE_ROOT."includes/util/notification.php");

// Handle comment posting.
if (isset($_POST['action'])) {
    // Skip if in maintenance mode.
    if (!CanPerformSitePost()) MaintenanceError();
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
    $clauses = array();
    foreach (explode(' ', $lower_search) as $term) {
        if (startswith($term, "id:")) {
            $term = substr($term, 3);
            $escaped_term = sql_escape($term);
            $clauses[] = "(PostId='$escaped_term')";
        } else if (startswith($term, "user:")) {
            $term = substr($term, 5);
            $escaped_term = sql_escape($term);
            $c1 = "(EXISTS(SELECT 1 FROM ".USER_TABLE." Q WHERE LOWER(DisplayName) LIKE '%$escaped_term%' AND Q.UserId=T.UserId))";
            $c2 = "(T.AdditionalUserIds != '' AND EXISTS(SELECT 1 FROM ".USER_TABLE." Q WHERE LOWER(DisplayName) LIKE '%$escaped_term%' AND FIND_IN_SET(Q.UserId, T.AdditionalUserIds)))";
            $clauses[] = "($c1 OR $c2)";
        } else if (startswith($term, "title:")) {
            $term = substr($term, 6);
            $escaped_term = sql_escape($term);
            $clauses[] = "(LOWER(Text) LIKE '%$escaped_term%')";
        } else {
            $escaped_term = sql_escape($term);
            $c1 = "(LOWER(Title) LIKE '%$escaped_term%')";
            $c2 = "(LOWER(Text) LIKE '%$escaped_term%')";
            $clauses[] = "($c1 OR $c2)";
        }
    }
    $post_sql_condition = join(" AND ", $clauses);
} else {
    $search = "";
}

// Get all parent posts.
$sql_comments_select = "*";
$sql_order = "WHERE ParentPostId=-1 AND (Status='A' OR Status='M') AND ($post_sql_condition) ORDER BY Timestamp DESC, PostId DESC";
CollectItems(OEKAKI_POST_TABLE, $sql_order, $posts, $posts_per_page, $iterator, "Error displaying oekaki posts.");
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
$user_ids = array();
foreach ($posts_by_id as &$post) {
    $user_ids[] = $post['UserId'];
    foreach (explode(",", $post['AdditionalUserIds']) as $id) {
        if (empty($id) || !is_numeric($id)) continue;
        $user_ids[] = $id;
    }
}
$user_ids = array_unique($user_ids);
if (LoadTableData(array(USER_TABLE), "UserId", $user_ids, $users_by_id)) {
    foreach ($users_by_id as &$usr) {
        $usr['avatarURL'] = GetAvatarURL($usr);
    }
    foreach ($posts_by_id as &$post) {
        $post['user'] = &$users_by_id[$post['UserId']];
        $additional = array();
        foreach (explode(",", $post['AdditionalUserIds']) as $id) {
            if (empty($id) || !is_numeric($id)) continue;
            $additional[] = &$users_by_id[(int)$id];
        }
        $post['additionalUsers'] = $additional;
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
$vars['search'] = $search;

// For now, show a banner to highlight the launch of the new oekaki.
$promotion_banner_enabled = true;
if ($promotion_banner_enabled) {
    PostBanner("Check out the new Oekaki! <a href='http://agn.ph/oekaki/draw/'>Draw Now</a>", "green", true, true);
}

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
    $username = $user['DisplayName'];
    $user_url = SITE_DOMAIN."/user/$uid/";
    $post_url = SITE_DOMAIN."/oekaki/?search=id%3A$post_id";
    $post_title = $post['Title'];
    AddNotification(
        /*user_id=*/$post['UserId'],
        /*title=*/"Comment on your Oekaki Artwork",
        /*contents=*/"<a href='$user_url'>$username</a> posted a comment on your Oekaki post <a href='$post_url'>$post_title</a>.",
        /*sender_id=*/$user['UserId']);
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