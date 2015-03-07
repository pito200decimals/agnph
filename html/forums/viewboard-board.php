<?php
// Views a forum board. Included from viewboard.php.
if (isset($user)) {
    $threads_per_page = $user['ThreadsPerPage'];
} else {
    $threads_per_page = DEFAULT_THREADS_PER_PAGE;
}
// Get lobby content.
$escaped_board = sql_escape($board_id);
if (sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId='$escaped_board';", 1)) {
    $board = $result->fetch_assoc();
    $lobby_id = $board['LobbyId'];
    // Check to see if we are a leaf.
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId='$lobby_id';", 0)) {
        if ($result->num_rows > 0) {
            // We have a child lobby. Just redirect to forums index (with hash to this id).
            header("Location: /forums/#b$board_id");
            return;
        }
        if (GetBreadcrumbsFromBoardId($board['LobbyId'], $names, $links)) {
            $vars['crumbs'] = CreateCrumbsHTML($names, $links);
            $board['threads'] = array();
            $threads = GetAllThreadsInLobby($board_id);
            if ($threads) {
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
                if (GetAllThreadCreatorData($threads)) {
                    $board['threads'] = $threads;
                    // Set output.
                    $vars['board'] = $board;
                } else {
                    // Error getting thread creator data.
                    $vars['content'] = "No forum boards to display.";
                }
            } else {
                // Board has no threads in it.
                $threads = array();
                $board['threads'] = array();
                $vars['board'] = $board;
            }
        } else {
            $vars['content'] = "Board not found.";
        }
    } else {
        $vars['content'] = "No forum boards to display.";
    }
} else {
    $vars['content'] = "No forum boards to display.";
}
?>