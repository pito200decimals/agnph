<?php
// Views a thread.
// URL: /forums/thread/{thread-id}/ => view_thread.php?thread=-1

include_once("../header.php");
include_once(SITE_ROOT."forums/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/listview.php");

if (!(isset($_GET['thread']) && is_numeric($_GET['thread']))) {
    RenderErrorPage("Thread not found");
}
$thread_id = (int)$_GET['thread'];
$thread = FetchThread($thread_id);
if ($thread == null) {
    RenderErrorPage("Thread not found");
}
$tid = $thread['ThreadId'];
$threads_per_page = GetPostsPerPageInThread();
CollectItems(FORUMS_POST_TABLE, "WHERE (PostId=$tid AND IsThread=1) OR (ParentId=$tid AND IsThread=0) ORDER BY PostDate ASC, PostId ASC", $posts, $threads_per_page, $iterator, "Thread not found");

InitPosters($posts);
$thread['posts'] = &$posts;
if (isset($user)) {
    $unread_post_ids = GetMixedUnreadPostIds($user);
    $maybe_read_up_to = $user['MaybeReadUpTo'];  // First index after end of mixed region. This and all indices after are all unread.
}
$unread_ids = array();
foreach ($posts as &$post) {
    $post['id'] = $post['PostId'];
    $post['date'] = FormatDate($post['PostDate'], FORUMS_DATE_FORMAT);
    if ($post['EditDate'] != 0) $post['editDate'] = FormatDate($post['EditDate'], FORUMS_DATE_FORMAT);
    $post['title'] = $post['Title'];
    if (isset($user)) {
        $post['actions'] = array();
        if (CanUserPostToThread($user, $thread)) {
            $post['actions'][] = array(
                "url" => "/forums/compose/",
                "action" => "reply",  // parameter id is already the post id.
                "method" => "GET",
                "label" => "Quote",
                "id" => $thread['ThreadId'],
                "kv" => array(array(
                        "key" => "quote",
                        "value" => $post['PostId']
                    ))
                );
        }
        if (CanUserEditForumsPost($user, $thread, $post)) {
            $post['actions'][] = array(
                "url" => "/forums/compose/",
                "action" => "edit",  // parameter id is already the post id.
                "method" => "GET",
                "label" => "Edit"
                );
        }
        if (CanUserDeleteForumsPost($user, $thread, $post)) {
            $post['actions'][] = array(
                // "url" => "",
                "action" => "delete-comment",
                "label" => "Delete",
                "confirmMsg" => "Are you sure you want to delete this post?"
                );
        }
    }
    $postContent = SanitizeHTMLTags($post['Text'], DEFAULT_ALLOWED_TAGS);
    $postSignature = SanitizeHTMLTags($post['user']['Signature'], DEFAULT_ALLOWED_TAGS);
    $post['text'] = "<div>$postContent</div><hr /><div class='signature'>$postSignature</div>";
    $post['anchor'] = "p".$post['PostId'];
    if (isset($user)) {
        if ($post['PostId'] >= $maybe_read_up_to || in_array($post['PostId'], $unread_post_ids)) {
            $post['unread'] = true;  // Not used, but may be useful in the future.
            $unread_ids[] = $post['PostId'];
        }
    }
}
$vars['thread'] = $thread;
if (isset($user)) {
    // Set up permissions.
    $vars['canReply'] = CanUserPostToThread($user, $thread);
}
GetBoard($thread['ParentBoardId'], $board);
// Create a fake "board" for this thread in the breadcrumb.
$vars['board'] = array(
    "Name" => $thread['Title'],
    "parentBoard" => $board,
    "linkUrl" => "/forums/thread/$tid/");
$vars['iterator'] = $iterator;

HandlePost();  // Handle post this late so we have thread already initialized.


// Update view count.
UpdateStatistics($thread);

// Mark posts as read.
if (isset($user)) {
    MarkPostsAsRead($user, $unread_ids);
}

RenderPage("forums/view_thread.tpl");
return;

function HandlePost() {
    global $user, $board, $thread, $posts;
    if (isset($user) && isset($_POST['action']) && isset($_POST['id']) && is_numeric($_POST['id'])) {
        if (!CanPerformSitePost()) MaintenanceError();
        // Try to perform action.
        $action_done = false;
        $action = $_POST['action'];
        $id = (int)$_POST['id'];
        switch ($action) {
            case "delete-comment":
                $posts_by_id = GetPostsById($posts);
                if (isset($posts_by_id[$id])) {
                    $post = $posts_by_id[$id];
                    $id = $post['PostId'];
                    if (CanUserDeleteForumsPost($user, $thread, $post)) {
                        // Delete post here.
                        sql_query("DELETE FROM ".FORUMS_POST_TABLE." WHERE PostId=$id;");
                        if ($post['IsThread'] == 0) {
                            // Update thread stats.
                            UpdateThreadStats($post['ParentId']);
                        } else {
                            // Thread was deleted, update board stats.
                            UpdateBoardStats($post['ParentId']);
                        }
                        sql_query("DELETE FROM ".FORUMS_UNREAD_POST_TABLE." WHERE PostId=$id;");
                        if (sizeof($posts) == 1) {
                            // Deleting only post on page.
                            if (isset($_GET['page']) && $_GET['page'] > 0) {
                                // Go back a page.
                                PostSessionBanner("Post deleted", "green");
                                $page = $_GET['page'];
                                if ($page == 1) {
                                    Redirect("/forums/thread/".$thread['ThreadId']."/");
                                } else {
                                    Redirect("/forums/thread/".$thread['ThreadId']."/?page=".($page - 1));
                                }
                            } else {
                                // Whole thread was deleted, go back to board.
                                PostSessionBanner("Thread deleted", "green");
                                Redirect("/forums/board/".urlencode(mb_strtolower($board['Name'], "UTF-8"))."/");
                            }
                        } else {
                            PostSessionBanner("Post deleted", "green");
                            Redirect($_SERVER['REQUEST_URI']);
                        }
                    } else {
                        PostSessionBanner("Not authorized to delete post", "red");
                        Redirect($_SERVER['REQUEST_URI']);
                    }
                }
                break;
            default:
                // No valid action specified.
                break;
        }
    }
}

function GetPostsById($posts) {
    $ret = array();
    foreach ($posts as $post) {
        $ret[$post['PostId']] = $post;
    }
    return $ret;
}

function UpdateStatistics($thread) {
    if (!IsMaintenanceMode()) {
        sql_query("UPDATE ".FORUMS_POST_TABLE." SET Views=Views+1 WHERE PostId=".$thread['ThreadId'].";");
    }
}
?>