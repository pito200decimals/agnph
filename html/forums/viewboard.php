<?php
// External page for viewing a board (list of threads), or a root board (list of more boards).

// Site includes, including login authentication.
include_once("../header.php");
include_once(__DIR__."/includes/functions.php");

if (isset($_GET['b']) && is_numeric($_GET['b'])) {
    $board_id = (int)$_GET['b'];
} else {
    $board_id = -1;
}
if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
    $threadoffset = (int)$_GET['offset'];
} else {
    // No offset set, just assume thread offset 0.
    $threadoffset = 0;
}

if ($board_id == -1) {
    // Root lobby.
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId=-1;", 0)) {
        $home = array();
        // Fetch all child lobbies.
        $child_ids = array();
        while ($row = $result->fetch_assoc()) {
            $id = $row['LobbyId'];
            $home[$id] = $row;
            $child_ids[] = $id;
        }
        $joined = implode(",", $child_ids);
        if (sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId IN ($joined);", 1)) {
            // Init child lobby data.
            while ($row = $result->fetch_assoc()) {
                $id = $row['LobbyId'];
                $pid = $row['ParentLobbyId'];
                $home[$pid]['childBoards'][$id] = $row;
            }
            // Set output.
            $vars['home'] = $home;
        } else {
            $vars['content'] = "No forum boards to display.";
        }
    } else {
        $vars['content'] = "No forum boards to display.";
    }
} else {
    // Child lobby.
    if (isset($user)) {
        $threads_per_page = $user['ThreadsPerPage'];
    } else {
        $threads_per_page = DEFAULT_THREADS_PER_PAGE;
    }
    // Get lobby content.
    $escaped_board = sql_escape($board_id);
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId='$escaped_board';", 1)) {
        $board = $result->fetch_assoc();
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
        $vars['content'] = "No forum boards to display.";
    }
}

// Default content.
$vars['content'] = "No forum boards to display.";

// Render page template.
echo $twig->render("forums/viewboard.tpl", $vars);
return;


?>