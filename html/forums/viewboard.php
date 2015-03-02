<?php
// External page for viewing a board (list of threads), or a root board (list of more boards).

// Site includes, including login authentication.
include_once("../header.php");

if (isset($_GET['b'])) {
    $board = $_GET['b'];
} else {
    $board = -1;
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
            debug($row);
            $id = $row['LobbyId'];
            $pid = $row['ParentLobbyId'];
            $vars['rootLobbies'][$pid]['lobbies'][$id] = $row;
        }
    } else {
        unset($vars['rootLobbies']);
    }
} else {
    // Child lobby.
    $vars['lobby']['threads'] = array();
    $result = sql_query("SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId=$board;");
    if ($result && $result->num_rows > 0) {
        $lobby = $result->fetch_assoc();
        $vars['lobby']['title'] = $lobby['Name'];
        $result = sql_query("SELECT * FROM ".FORUMS_THREAD_TABLE." WHERE ParentLobbyId=$board ORDER BY Sticky DESC, ThreadId;");
        if ($result) {
            $user_ids = array();
            while ($row = $result->fetch_assoc()) {
                $tid = $row['ThreadId'];
                $vars['lobby']['threads'][$tid] = $row;
                $user_ids[] = $row['CreatorUserId'];
            }
            $users = array();
            LoadUsers($user_ids, $users);
            foreach ($vars['lobby']['threads'] as &$thread) {
                $thread['creator'] = $users[$thread['CreatorUserId']];
            }
        } else {
            // TODO: Better error condition. Display informative message?
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
?>