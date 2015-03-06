<?php
// Page where a user can view a thread.
// URL format: /forums/thread/{thread-id}/{post-offset}/#p{post-id}
// Rewritten format: /forums/thread/viewthread.php?t={thread-id}&p={post-offset}#p{post-id}

// Site includes, including login authentication.
include_once("../../header.php");
include_once(__DIR__."/../includes/functions.php");

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
$escaped_tid = sql_escape($tid);
if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_tid';", 1)) {
    $thread = $result->fetch_assoc();
    // Load creator of thread.
    if (LoadUser($thread['UserId'], $thread['creator'])) {
        // Get all posts.
        $posts = GetAllPostsInThread($thread['PostId']);
        if ($posts) {
            SetPostLinks($posts, false);
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
            if (GetAllPosterData($posts)) {
                if (GetBreadcrumbsFromPost($thread, $names, $links)) {
                    $thread['Posts'] = $posts;
                    $vars['thread'] = $thread;
                    $vars['crumbs'] = CreateCrumbsHTML($names, $links);
                } else {
                    $vars['content'] = "Thread not found";
                }
            } else {
                // Can't load data for posts' user info.
                $vars['content'] = "Thread not found";
            }
        } else {
            // Couldn't load posts in thread.
            $vars['content'] = "Thread not found";
        }
    } else {
        // Can't load creator data.
        $vars['content'] = "Thread not found";
    }
} else {
    // Can't find thread.
    $vars['content'] = "Thread not found";
}

// Default content.

// Render page template.
RenderPage("forums/thread/viewthread.tpl");
return;
?>