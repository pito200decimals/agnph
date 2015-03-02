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
if (!isset($_GET['offset'])) {
    // No offset set, just assume post offset 0.
    $postoffset = 0;
} else {
    $postoffset = $_GET['offset'];
}

// Get thread content.
if (isset($user)) {
    $posts_per_page = $user['PostsPerPage'];
} else {
    $posts_per_page = DEFAULT_POSTS_PER_PAGE;
}
// Query for thread data.
$result = sql_query("SELECT * FROM ".FORUMS_THREAD_TABLE." WHERE ThreadId=$tid;");
if ($result && $result->num_rows > 0) {
    $thread = $result->fetch_assoc();
    // Load creator of thread.
    if (!LoadUser($thread['CreatorUserId'], $thread['creator'])) {
        unset($thread);
    } else {
        // Get all posts.
        $posts = GetAllPostsInThread($thread['ThreadId']);
        if ($posts) {
            $vars['page_iterator'] = Paginate($posts, $postoffset, $posts_per_page,
                function($i, $txt, $curr_page) use ($tid, $posts_per_page) {
                    if ($i == $curr_page) {
                        return "$txt";
                    } else {
                        $offset = ($i - 1) * $posts_per_page;
                        return "<a href='/forums/thread/$tid/$offset/' style='margin-left:3px;margin-right:3px;text-decoration:none;'>$txt</a>";
                    }
                });
            // Get poster user data for each post.
            GetAllPosterData($posts);
            $thread['Posts'] = $posts;
            $vars['thread'] = $thread;
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
function GetAllPostsInThread($tid) {
    //$result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId IN ($postcsvlist);");
    $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId=$tid;");
    if (!$result) return null;
    $posts = array();
    while ($row = $result->fetch_assoc()) {
        $posts[$row['PostId']] = $row;
    }
    return $posts;
}

function GetAllPosterData(&$posts) {
    $uids = array();
    foreach ($posts as $post) {
        $uids[] = $post['UserId'];
    }
    $users = array();
    LoadUsers($uids, $users, array(FORUMS_USER_PREF_TABLE));
    foreach ($posts as &$post) {
        $post['poster'] = $users[$post['UserId']];
        $post['PostDate'] = FormatDate($post['PostDate']);
        if ($post['EditDate'] != 0) {
            $post['EditDate'] = FormatDate($post['EditDate']);
        } else {
            // Don't display EditDate.
            unset($post['EditDate']);
        }
    }
}
?>