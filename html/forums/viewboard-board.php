<?php
// Views a forum board. Included from viewboard.php.
if (isset($user)) {
    $threads_per_page = $user['ThreadsPerPage'];
} else {
    $threads_per_page = DEFAULT_THREADS_PER_PAGE;
}
$unread_posts = array();
$unread_threads = array();
$unread_boards = array();
if (isset($user)) {
    debug("Getting unread posts");
    GetUnreadPostIds($user, $unread_posts, $unread_threads, $unread_boards) or RenderErrorPage("Board not found.");
}

// Get lobby content.
debug("Getting lobby");
$escaped_board = sql_escape($board_id);
sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId='$escaped_board';", 1) or RenderErrorPage("No forum boards to display.");
$board = $result->fetch_assoc();
$lobby_id = $board['LobbyId'];

// Check to see if we are a leaf.
sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId='$lobby_id';", 0) or RenderErrorPage("No forum boards to display.");
if ($result->num_rows > 0) {
    // We have a child lobby. Just redirect to forums index (with hash to this id).
    header("Location: /forums/#b$board_id");
    return;
}

// Construct board breadcrumbs.
debug("Creating crumbs");
GetBreadcrumbsFromBoardId($board['LobbyId'], $names, $links) or RenderErrorPage("Board not found.");
$vars['crumbs'] = CreateCrumbsHTML($names, $links);

// Get all threads
debug("Fetching threads");
$board['threads'] = array();
$threads = GetAllThreadsInLobby($board_id);
if ($threads) {
    debug("Paginating");
    $vars['page_iterator'] = Paginate($threads, $threadoffset, $threads_per_page,
        function($i, $txt, $curr_page) use ($board, $threads_per_page) {
            if ($i == $curr_page) {
                return "$txt";
            } else {
                $offset = ($i - 1) * $threads_per_page;
                return "<a href='/forums/board/$board/$offset/' style='margin-left:3px;margin-right:3px;text-decoration:none;'>$txt</a>";
            }
        });
    // Get creator user data for each thread.
    debug("Getting creator data");
    GetAllThreadCreatorData($threads) or RenderErrorPage("No forum boards to display.");

    // Mark threads as unread if needed, and get their first-unread-post links.
    debug("Fetching unread posts in each thread");
    foreach ($threads as &$thread) {
        $tid = $thread['PostId'];
        if (in_array($tid, $unread_threads)) {
            $thread['unread'] = true;
            $posts = GetAllPostsInThread($thread['PostId']) or RenderErrorPage("Board not found.");
            $post_offset = 0;
            $post_id = -1;
            foreach ($posts as $post) {
                if (in_array($post['PostId'], $unread_posts)) {
                    $post_id = $post['PostId'];
                    break;
                } else {
                    $post_offset++;
                }
            }
            $thread['unread_link'] = "/forums/thread/$tid/$post_offset/#p$post_id";
        }
    }
    $board['threads'] = $threads;
    // Set output.
    $vars['board'] = $board;
    if (isset($user) && CanUserPostToBoard($user, $board['LobbyId'])) $vars['user']['canPostToBoard'] = true;
} else {
    // Board has no threads in it.
    $threads = array();
    $board['threads'] = array();
    $vars['board'] = $board;
}
?>