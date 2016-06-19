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
            $vars['canLockOrSticky'] = CanUserLockStickyOrMarkNewsThread($user);
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
            SetUpThreadPostHistory($posts);
            $thread['posts'] = $posts;
            $vars['thread'] = $thread;
            GetBoard($thread['ParentBoardId'], $board);
            // Create a fake "board" for this thread in the breadcrumb.
            $vars['board'] = array(
                "Name" => $thread['Title'],
                "parentBoard" => $board,
                "linkUrl" => "/forums/thread/$tid/");
            if (isset($_GET['quote']) && is_numeric($_GET['quote'])) {
                $qpid = (int)$_GET['quote'];
                $escaped_qpid = sql_escape($qpid);
                if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_qpid';", 1)) {
                    $row = $result->fetch_assoc();
                    $vars['quoteDate'] = FormatDate($row['PostDate'], FORUMS_QUOTE_DATE_FORMAT);
                    $vars['quoteText'] = $row['Text'];
                    $quid = $row['UserId'];
                    $vars['quoteUserId'] = $quid;
                    if (sql_query_into($result, "SELECT DisplayName FROM ".USER_TABLE." WHERE UserId=$quid;", 1)) {
                        $vars['quoteUser'] = $result->fetch_assoc()['DisplayName'];
                    }
                }
            }
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
            GetBoard($thread['ParentBoardId'], $board);
            // Create a fake "board" for this thread in the breadcrumb.
            $vars['board'] = array(
                "Name" => $thread['Title'],
                "parentBoard" => $board,
                "linkUrl" => "/forums/thread/$tid/");
            $vars['canLockOrSticky'] = (($post['IsThread'] == 1) && CanUserLockStickyOrMarkNewsThread($user));
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
    if (!isset($_POST['submit'])) return;
    $action = ValidParams($_POST);
    if (!$action) return;
    GetPostObject($action, $_POST);
    if (!CanPerformSitePost()) MaintenanceError();
    if ($action == "create") HandleCreateThread();
    if ($action == "reply") HandleReplyThread();
    if ($action == "edit") HandleEditPost();
}

// Note: Below, PostBanner and POSTS are kept so that input text is preserved.

function HandleCreateThread() {
    global $user, $vars;
    if (isset($_POST['title']) &&
        isset($_POST['text'])) {
        $title = $_POST['title'];
        $text = $_POST['text'];
        if (CanUserLockStickyOrMarkNewsThread($user)) {
            $sticky = isset($_POST['sticky']) ? "1" : "0";
            $locked = isset($_POST['locked']) ? "1" : "0";
            $news = isset($_POST['news']) ? "1" : "0";
        } else {
            $sticky = "0";
            $locked = "0";
            $news = "0";
        }
        if (mb_strlen($title) == 0) {
            PostBanner("Missing post title", "red");
            return;
        }
        if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) {
            PostBanner("Post not long enough", "red");
            return;
        }
        $sanitizedText = GetSanitizedTextTruncated($text, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape(GetSanitizedTextTruncated($_POST['title'], DEFAULT_ALLOWED_TAGS, MAX_FORUMS_POST_TITLE_LENGTH));
        $escaped_text = sql_escape($sanitizedText);
        $now = time();
        $uid = $user['UserId'];
        $bid = $vars['id'];
        sql_query("INSERT INTO ".FORUMS_POST_TABLE."
            (UserId, Title, Text, PostDate, ParentId, IsThread, Replies, LastPostDate, Sticky, Locked, NewsPost)
            VALUES
            ($uid, '$escaped_title', '$escaped_text', $now, $bid, 1, 0, $now, $sticky, $locked, $news);");
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
        if (mb_strlen($title) == 0) {
            PostBanner("Missing post title", "red");
            return;
        }
        if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) {
            PostBanner("Post not long enough", "red");
            return;
        }
        $sanitizedText = GetSanitizedTextTruncated($text, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape(GetSanitizedTextTruncated($_POST['title'], DEFAULT_ALLOWED_TAGS, MAX_FORUMS_POST_TITLE_LENGTH));
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
        $title = $_POST['title'];
        $text = $_POST['text'];
        if (mb_strlen($title) == 0) {
            PostBanner("Missing post title", "red");
            return;
        }
        if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) {
            PostBanner("Post not long enough", "red");
            return;
        }
        $sanitizedText = GetSanitizedTextTruncated($text, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_POST_LENGTH);
        $escaped_title = sql_escape(GetSanitizedTextTruncated($title, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_POST_TITLE_LENGTH));
        $sets[] = "Title='$escaped_title'";

        $escaped_text = sql_escape($sanitizedText);
        $sets[] = "Text='$escaped_text'";

        if (CanUserLockStickyOrMarkNewsThread($user)) {
            $sticky = isset($_POST['sticky']) ? "1" : "0";
            $locked = isset($_POST['locked']) ? "1" : "0";
            $news = isset($_POST['news']) ? "1" : "0";
            $sets[] = "Sticky=$sticky";
            $sets[] = "Locked=$locked";
            $sets[] = "NewsPost=$news";
        }
        $boards_to_update = array();
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
                if ($new_bid != $vars['post']['ParentId']) {
                    $boards_to_update[] = $vars['post']['ParentId'];
                    $boards_to_update[] = $new_bid;
                    $sets[] = "ParentId='$escaped_bid'";
                    LogAction("<strong><a href='/user/$uid/'>$name</a></strong> moved thread <strong>$title</strong> to board <strong>$boardname</strong>", "R");
                }
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
        if (sizeof($boards_to_update) > 0) {
            foreach ($boards_to_update as $bid) {
                UpdateBoardStats($bid);
            }
        }
        GoToForumPost($pid);
    } else {
        PostBanner("Invalid action", "red");
    }
}

function GoToForumPost($pid) {
    $posts_per_page = GetPostsPerPageInThread();
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$pid;", 1)) {
        Redirect("/forums/");
    }
    $post = $result->fetch_assoc();
    if ($post['IsThread'] == 1) {
        $tid = $post['PostId'];
    } else {
        $tid = $post['ParentId'];
    }
    $posts = GetPostsInThread($tid) or Redirect("/forums/");
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
    Redirect("/forums/thread/$tid/?page=$page#p$pid");
}

function SetUpThreadPostHistory(&$posts) {
    foreach ($posts as &$post) {
        $post['id'] = $post['PostId'];
        $post['date'] = FormatDate($post['PostDate'], FORUMS_DATE_FORMAT);
        if ($post['EditDate'] != 0) $post['editDate'] = FormatDate($post['EditDate'], FORUMS_DATE_FORMAT);
        $post['title'] = $post['Title'];
        $post['text'] = "<div>".$post['Text']."</div>";
    }
}

?>