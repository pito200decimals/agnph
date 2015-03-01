<?php
// Page where a user can view a thread.
// URL format: /forums/thread/{thread-id}/{post-offset}/#p{post-id}
// Rewritten format: /forums/thread/viewthread.php?t={thread-id}&p={post-offset}#p{post-id}

// Site includes, including login authentication.
include_once("../../header.php");

if (!isset($_GET['t'])) {
    // No thread, quit.
    $vars['content'] = "Thread not found.";
    RenderPage("forums/thread/viewthread.tpl");
    return;
}
$tid = $_GET['t'];
if (!isset($_GET['p'])) {
    // No offset set, just assume post offset 0.
    $postoffset = 0;
} else {
    $postoffset = $_GET['p'];
    debug("Viewing post offset $postoffset");
}

// Get thread content.
$user = LoadLoggedInUserAllData($user['UserId']);
$posts_per_page = $user['PostsPerPage'];
$curr_page = floor($postoffset / $posts_per_page) + 1;

$result = sql_query("SELECT * FROM ".FORUMS_THREAD_TABLE." WHERE ThreadId=$tid;");
if ($result && $result->num_rows > 0) {
    $thread = $result->fetch_assoc();
    if (!LoadUser($thread['CreatorUserId'], $thread['creator'])) {
        unset($thread);
    } else {
        // TODO: Filter out only a limited set per page.
        $num_posts_in_thread = sizeof(explode(",", $thread['Posts']));
        $max_pages = ceil($num_posts_in_thread / $posts_per_page);
        $posts = GetPostsFromCSVList($thread['Posts'], $postoffset, $posts_per_page);
        if ($posts) {
            $thread['Posts'] = $posts;
            $vars['thread'] = $thread;
            $vars['page_iterator'] = ConstructPageIterator($curr_page, $max_pages, DEFAULT_PAGE_ITERATOR_SIZE, function($i, $txt) use ($tid, $curr_page, $posts_per_page) {
                if ($i == $curr_page) {
                    return "$txt";
                } else {
                    $offset = ($i - 1) * $posts_per_page;
                    return "<a href='/forums/thread/$tid/$offset/' style='margin-left:3px;margin-right:3px;text-decoration:none;'>$txt</a>";
                }
            });
        } else {
            unset($thread);
        }
    }
} else {
    unset($thread);
}

// Default content.
$vars['content'] = "Thread not found";

// Render page template.
RenderPage("forums/thread/viewthread.tpl");
return;

// General helper functions.
function GetPostsFromCSVList($postcsvlist, $postoffset, $max_count) {
    $post_ids = explode(",", $postcsvlist);
    $post_ids = array_slice($post_ids, $postoffset, $max_count);
    $postcsvlist = implode(",", $post_ids);
    $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId IN ($postcsvlist)");
    if (!$result) return null;
    $posts = array();
    $uids = array();
    while ($row = $result->fetch_assoc()) {
        $posts[$row['PostId']] = $row;
        $uids[] = $row['UserId'];
    }
    $retlist = array();
    $users = array();
    LoadUsers($uids, $users);
    mt_srand(time());
    foreach ($post_ids as $pid) {
        $post = array();
        $post['PostId'] = $posts[$pid]['PostId'];
        $post['poster'] = $users[$posts[$pid]['UserId']];
        $post['Content'] = $posts[$pid]['Content'];
        $post['PostDate'] = FormatDate($posts[$pid]['PostDate']);
        $post['EditDate'] = FormatDate($posts[$pid]['EditDate']);
        $retlist[] = $post;
    }
    return $retlist;
}
?>