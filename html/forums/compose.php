<?php
// Composes/edits a post/thread.
// URL: /forums/compose/?action={action}&id={id}

include_once("../header.php");
include_once(SITE_ROOT."forums/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");

HandlePost();
$action = ValidParams($_GET) or InvalidURL();
GetPostObject($action, $_GET);

$vars['action'] = $action;

RenderPage("forums/compose.tpl");
return;

function ValidParams($params) {
    global $user;
    if (!isset($params['action'])) return false;
    $action = $params['action'];
    switch ($action) {
        case "create":
            if (!isset($params['id'])) return false;
            if (!is_numeric($params['id'])) return false;
            break;
        case "reply":
            if (!isset($params['id'])) return false;
            if (!is_numeric($params['id'])) return false;
            break;
        case "edit":
            if (!isset($params['id'])) return false;
            if (!is_numeric($params['id'])) return false;
            break;
        default:
            return false;
    }
    if (!isset($user)) RenderErrorPage("Must be logged in to $action a post");
    return $action;
}

function GetPostObject($action, $params) {
    global $user, $vars;
    switch ($action) {
        case "create":
            $bid = (int)$params['id'];
            GetBoard($bid, $board) or RenderErrorPage("Board does not exist");
            if (!CanUserCreateThread($user, $board)) RenderErrorPage("Not authorized to post to this board");
            $bid = $board['BoardId'];
            $vars['id'] = $bid;
            $vars['board'] = $board;
            $vars['canLockOrSticky'] = CanUserLockOrStickyThread($user);
            return $board;
        case "reply":
            $tid = (int)$params['id'];
            $thread = FetchThread($tid);
            if ($thread == null) RenderErrorPage("Thread does not exist");
            if (!CanUserPostToThread($user, $thread)) RenderErrorPage("Not authorized to post to this thread");
            $tid = $thread['ThreadId'];
            $vars['id'] = $tid;
            $posts = GetPostsInThread($thread);
            InitPosters($posts);
            $thread['posts'] = $posts;
            $vars['thread'] = $thread;
            return $thread;
        case "edit":
            $pid = (int)$params['id'];
            $escaped_pid = sql_escape($pid);
            sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_pid';", 1) or RenderErrorPage("Post does not exist");
            $post = $result->fetch_assoc();
            if ($post['IsThread'] == 1) {
                $tid = $post['PostId'];
            } else {
                $tid = $post['ParentId'];
            }
            $thread = FetchThread($tid);
            if (!CanUserEditForumsPost($user, $thread, $post)) RenderErrorPage("Not authorized to edit this post");
            $pid = $post['PostId'];
            $vars['id'] = $pid;
            $vars['post'] = $post;
            $vars['canLockOrSticky'] = (($post['IsThread'] == 1) && CanUserLockOrStickyThread($user));
            return $post;
        default:
            InvalidURL();
    }
}

function HandlePost() {
    $action = ValidParams($_POST);
    if (!$action) return;
    GetPostObject($action, $_POST);
    if ($action == "create") HandleCreateThread();
    if ($action == "reply") HandleReplyThread();
    if ($action == "edit") HandleEditPost();
}

function HandleCreateThread() {
    global $user, $vars;
    if (isset($_POST['title']) &&
        // isset($_POST['icon']) &&
        isset($_POST['text'])) {
        $title = $_POST['title'];
        $text = $_POST['text'];
        // $icon = $_POST['icon'];
        if (CanUserLockOrStickyThread($user)) {
            $sticky = isset($_POST['sticky']) ? "1" : "0";
            $locked = isset($_POST['locked']) ? "1" : "0";
        } else {
            $sticky = "0";
            $locked = "0";
        }
        $sanitizedText = GetSanitizedTextTruncated($text, MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape($_POST['title']);
        $escaped_text = sql_escape($sanitizedText);
        $now = time();
        $uid = $user['UserId'];
        $bid = $vars['id'];
        sql_query("INSERT INTO ".FORUMS_POST_TABLE."
            (UserId, Title, Text, PostDate, ParentId, IsThread, Replies, LastPostDate, Sticky, Locked)
            VALUES
            ($uid, '$escaped_title', '$escaped_text', $now, $bid, 1, 1, $now, $sticky, $locked);");
        $pid = sql_last_id();
        // TODO: Update board stats.
        PostSessionBanner("Thread created", "green");
        MarkPostsAsRead($user, array($pid));
        GoToForumPost($pid);
    } else {
        PostBanner("Invalid", "red");
    }
}
function HandleReplyThread() {
    global $user, $vars;
    if (isset($_POST['title']) &&
        // isset($_POST['icon']) &&
        isset($_POST['text'])) {
        $title = $_POST['title'];
        $text = $_POST['text'];
        // $icon = $_POST['icon'];
        $sanitizedText = GetSanitizedTextTruncated($text, MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape($_POST['title']);
        $escaped_text = sql_escape($sanitizedText);
        $now = time();
        $uid = $user['UserId'];
        $tid = $vars['id'];
        sql_query("INSERT INTO ".FORUMS_POST_TABLE."
            (UserId, Title, Text, PostDate, ParentId, IsThread)
            VALUES
            ($uid, '$escaped_title', '$escaped_text', $now, $tid, 0);");
        $pid = sql_last_id();
        UpdateThreadStats($tid);
        // TODO: Update board stats.
        PostSessionBanner("Reply posted", "green");
        MarkPostsAsRead($user, array($pid));
        GoToForumPost($pid);
    } else {
        PostBanner("Invalid", "red");
    }
}
function HandleEditPost() {
    global $user, $vars;
    if (isset($_POST['title']) &&
        // isset($_POST['icon']) &&
        isset($_POST['text'])) {
        $title = $_POST['title'];
        $text = $_POST['text'];
        // $icon = $_POST['icon'];
        if (CanUserLockOrStickyThread($user)) {
            $sticky = isset($_POST['sticky']) ? "1" : "0";
            $locked = isset($_POST['locked']) ? "1" : "0";
            $sticky_lock_sets = "Sticky=$sticky, Locked=$locked,";
        } else {
            $sticky_lock_sets = "";
        }
        $sticky = isset($_POST['sticky']) ? "1" : "0";
        $locked = isset($_POST['locked']) ? "1" : "0";
        $sanitizedText = GetSanitizedTextTruncated($text, MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape($_POST['title']);
        $escaped_text = sql_escape($sanitizedText);
        $now = time();
        $uid = $user['UserId'];
        $pid = $vars['id'];
        sql_query("UPDATE ".FORUMS_POST_TABLE." SET
            Title='$escaped_title',
            Text='$escaped_text',
            $sticky_lock_sets
            EditDate=$now
            WHERE PostId=$pid;");
        UpdateThreadStats($vars['post']['ParentId']);
        PostSessionBanner("Saved post changes", "green");
        GoToForumPost($pid);
    } else {
        PostBanner("Invalid", "red");
    }
}

function GetSanitizedTextTruncated($text, $max_byte_size){
    $sanitized = GetSanitizedText($text);
    while (strlen($sanitized) > $max_byte_size) {  // Use byte-size here, not mb_char size.
        $text = mb_substr($text, 0, mb_strlen($text) - 1);
        $sanitized = GetSanitizedText($text);
    }
    return $sanitized;
}

function GetSanitizedText($text) {
    return SanitizeHTMLTags($text, DEFAULT_ALLOWED_TAGS);
}

function GoToForumPost($pid) {
    $posts_per_page = GetPostsPerPageInThread();
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$pid;", 1)) {
        header("Location: /forums/");
        exit();
    }
    $post = $result->fetch_assoc();
    if ($post['IsThread'] == 1) {
        $tid = $post['PostId'];
    } else {
        $tid = $post['ParentId'];
    }
    $posts = GetPostsInThread($tid) or header("Location: /forums/");
    $offset = 0;
    $page = 1;
    foreach ($posts as $post) {
        if ($post['PostId'] == $pid) break;
        $offset++;
        if ($offset == $posts_per_page) {
            $offset = 0;
            $page++;
        }
    }
    header("Location: /forums/thread/$tid/?page=$page#p$pid");
    exit();
}
?>