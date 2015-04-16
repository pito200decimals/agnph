<?php
// Page where a user can view a thread.
// URL format: /forums/thread/{thread-id}/{post-offset}/#p{post-id}
// Rewritten format: /forums/thread/viewthread.php?t={thread-id}&p={post-offset}#p{post-id}

// Site includes, including login authentication.
include_once("../../header.php");
include_once(__DIR__."/../includes/functions.php");

if (!isset($_GET['t'])) {
    // No thread, quit.
    RenderErrorPage("Thread not found.");
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
    $posts_per_page = $user['ForumPostsPerPage'];
} else {
    $posts_per_page = DEFAULT_FORUM_POSTS_PER_PAGE;
}
// Query for thread data.
$escaped_tid = sql_escape($tid);
sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_tid';", 1) or RenderErrorPage("Thread not found.");
$thread = $result->fetch_assoc();

// Load creator of thread.
LoadSingleTableEntry(array(USER_TABLE), "UserId", $thread['UserId'], $thread['creator']) or RenderErrorPage("Thread not found.");

// Get all posts in the thread.
$posts = GetAllPostsInThread($thread['PostId']) or RenderErrorPage("Thread not found.");
SetPostLinks($posts, false);

// Construct the thread page iterator. Also slices the posts to only the viewed page.
$vars['page_iterator'] = Paginate($posts, $postoffset, $posts_per_page,
    function($i, $curr_page, $max_page) use ($tid, $posts_per_page) {
        if ($i == $curr_page) {
            return "[$i]";
        } else {
            $offset = ($i - 1) * $posts_per_page;
            return "<a href='/forums/thread/$tid/$offset/' style='margin-left:3px;margin-right:3px;text-decoration:none;'>$i</a>";
        }
    });


// Get poster user data for each post.
GetAllPosterData($posts) or RenderErrorPage("Thread not found.");
$thread['Posts'] = $posts;
$vars['thread'] = $thread;
if (isset($user) && CanUserPostToThread($user, $thread)) $vars['user']['canPostToThread'] = true;

// Construct breadcrumb trail for this thread.
GetBreadcrumbsFromPost($thread, $names, $links) or RenderErrorPage("Thread not found.");
$vars['crumbs'] = CreateCrumbsHTML($names, $links);

if (isset($user)) $vars['deletehash'] = md5($user['UserId'].$user['Password']);

// Render page template.
RenderPage("forums/thread/viewthread.tpl");
if (isset($user)) {
    // Mark visible posts as read (Only after rendering stuff.
    $read_post_ids = array_map(function($post){ return $post['PostId']; }, $posts);
    $read_post_ids = array_reverse($read_post_ids);
    MarkPostsAsRead($user, $read_post_ids);
}
return;
?>