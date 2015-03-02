<?php
// External page for viewing a board (list of threads), or a root board (list of more boards).

// Site includes, including login authentication.
include_once("../header.php");

if (isset($_GET['b'])) {
    $board = $_GET['b'];
} else {
    $board = -1;
}
if (!isset($_GET['offset'])) {
    // No offset set, just assume thread offset 0.
    $threadoffset = 0;
} else {
    $threadoffset = $_GET['offset'];
}

if ($board == -1) {
    // Root lobby.
    $vars['rootLobbies'] = array();
    $result = sql_query("SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId=-1;");
    $child_ids = array();
    while ($row = $result->fetch_assoc()) {
        $id = $row['LobbyId'];
        $vars['rootLobbies'][$id] = $row;
        $child_ids[] = $id;
    }
    $joined = implode(",", $child_ids);
    $result = sql_query("SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId IN ($joined);");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['LobbyId'];
            $pid = $row['ParentLobbyId'];
            $vars['rootLobbies'][$pid]['lobbies'][$id] = $row;
        }
    } else {
        unset($vars['rootLobbies']);
    }
} else {
    // Child lobby.
    if (isset($user)) {
        $threads_per_page = $user['ThreadsPerPage'];
    } else {
        $threads_per_page = DEFAULT_THREADS_PER_PAGE;
    }
    // Get lobby content.
    $vars['lobby']['threads'] = array();
    $result = sql_query("SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId=$board;");
    if ($result && $result->num_rows > 0) {
        $vars['lobby'] = $result->fetch_assoc();
        $threads = GetAllThreadsInLobby($board);
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
            GetAllThreadCreatorData($threads);
            $vars['lobby']['threads'] = $threads;
        } else {
            unset($vars['lobby']);
        }
    } else {
        unset($vars['lobby']);
    }
}

// Default content.
$vars['content'] = "No forum boards to display.";

// Render page template.
echo $twig->render("forums/viewboard.tpl", $vars);
return;

function GetAllThreadsInLobby($board) {
    $result = sql_query("SELECT * FROM ".FORUMS_THREAD_TABLE." WHERE ParentLobbyId=$board ORDER BY Sticky DESC, ThreadId DESC;");
    if ($result) {
        $threads = array();
        while ($row = $result->fetch_assoc()) {
            $thread = $row;
            $tid = $thread['ThreadId'];
            $thread['CreateDate'] = FormatDate($thread['CreateDate']);
            $threads[$tid] = $thread;
        }
        return $threads;
    } else {
        return null;
    }
}

function GetAllThreadCreatorData(&$threads) {
    $user_ids = array();
    foreach ($threads as $thread) {
        $user_ids[] = $thread['CreatorUserId'];
    }
    $users = array();
    LoadUsers($user_ids, $users);
    foreach ($threads as &$thread) {
        $thread['creator'] = $users[$thread['CreatorUserId']];
    }
}

?>