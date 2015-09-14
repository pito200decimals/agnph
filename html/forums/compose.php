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
            $vars['thread'] = $thread;
            $vars['canLockOrSticky'] = (($post['IsThread'] == 1) && CanUserLockOrStickyThread($user));
            $vars['canMoveThread'] = (($post['IsThread'] == 1) && CanUserMoveThread($user));
            if ($vars['canMoveThread']) {
                $vars['allBoards'] = GetOrderedBoardTree();
            }
            return $post;
        default:
            InvalidURL();
    }
}

function HandlePost() {
    if (!CanPerformSitePost()) MaintenanceError();
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
        isset($_POST['text'])) {
        $title = $_POST['title'];
        $text = $_POST['text'];
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
        UpdateBoardStats($bid);
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
        isset($_POST['text'])) {
        $title = $_POST['title'];
        $text = $_POST['text'];
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
        if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$tid AND IsThread=1;", 1)) {
            UpdateBoardStats($result->fetch_assoc()['ParentId']);
        }
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
        isset($_POST['text'])) {

        $sets = array();
        $sanitizedText = GetSanitizedTextTruncated($_POST['text'], MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape($_POST['title']);
        $sets[] = "Title='$escaped_title'";

        $escaped_text = sql_escape($sanitizedText);
        $sets[] = "Text='$escaped_text'";

        if (CanUserLockOrStickyThread($user)) {
            $sticky = isset($_POST['sticky']) ? "1" : "0";
            $locked = isset($_POST['locked']) ? "1" : "0";
            $sets[] = "Sticky=$sticky";
            $sets[] = "Locked=$locked";
        }
        if (CanUserMoveThread($user) && isset($_POST['move-board']) && is_numeric($_POST['move-board'])) {
            // Assume "move-board" will not be set on non-threads (as only authenticated administrators can do this anyways).
            $new_bid = (int)$_POST['move-board'];
            $escaped_bid = sql_escape($new_bid);
            $uid = $user['UserId'];
            $name = $user['DisplayName'];
            $title = $_POST['title'];
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$new_bid;", 1)) {
                $board = $result->fetch_assoc();
                $boardname = $board['Name'];
                $sets[] = "ParentId='$escaped_bid'";
                LogAction("<strong><a href='/user/$uid/'>$name</a></strong> moved thread <strong>$title</strong> to board <strong>$boardname</strong>", "R");
            } else {
                PostSessionBanner("Board does not exist", "red");
            }
        }
        $now = time();
        $uid = $user['UserId'];
        $pid = $vars['id'];
        if (sizeof($sets) > 0) {
            sql_query("UPDATE ".FORUMS_POST_TABLE." SET ".implode(",", $sets)." WHERE PostId=$pid;");
            UpdateThreadStats($vars['post']['ParentId']);
            UpdateBoardStats($vars['thread']['ParentId']);
            if (isset($new_bid)) {
                UpdateBoardStats($new_bid);
            }
            PostSessionBanner("Saved post changes", "green");
        }
        GoToForumPost($pid);
    } else {
        PostBanner("Invalid action", "red");
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

function GetOrderedBoardTree() {
    $root = array("BoardId" => -1);
    InitBoardChildren($root);
    $ret = array();
    RecursivelyFillBoardList($ret, $root, -1);
    return $ret;
}

function RecursivelyFillBoardList(&$list, $board, $depth) {
    global $user;
    if (CanUserViewBoard($user, $board)) {
        if ($depth >= 0) {
            $board['depth'] = $depth;
            $list[] = $board;
        }
        foreach ($board['childBoards'] as $cb) {
            RecursivelyFillBoardList($list, $cb, $depth + 1);
        }
    }
}

function OrderAndAssignDepths(&$boards) {
    $boards_by_id = array();
    foreach ($boards as &$b) {
        $boards_by_id[$b['BoardId']] = &$b;
    }
    foreach ($boards_by_id as $bid => $board) {
        AssignDepth($bid, $boards_by_id);
    }
}

function AssignDepth($bid, &$boards_by_id) {
    $board = &$boards_by_id[$bid];
    if (isset($board['depth'])) return $board['depth'];
    $pid = $board['ParentId'];
    if ($pid == -1) {
        $board['depth'] = 0;
        return 0;
    }
    $board['depth'] = AssignDepth($board['ParentId'], $boards_by_id) + 1;
    return $board['depth'];
}

function AssignRoots($bid, &$boards_by_id) {
    $board = &$boards_by_id[$bid];
    if (isset($board['rootId'])) return $board['rootId'];
    $pid = $board['ParentId'];
    if ($pid == -1) {
        $board['rootId'] = $bid;
        return $bid;
    }
    $board['rootId'] = AssignRoots($board['ParentId'], $boards_by_id);
    return $board['rootId'];
}
?>