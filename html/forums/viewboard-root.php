<?php
// Views the forum home page. Included from viewboard.php.

$unread_posts = array();
$unread_threads = array();
$unread_boards = array();
if (isset($user)) {
    GetUnreadPostIds($user, $unread_posts, $unread_threads, $unread_boards) or RenderErrorPage("Board not found.");
}

// Get global board groups.
sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId=-1;", 0) or RenderErrorPage("No forum boards to display.");
$home = array();

// Fetch all child lobbies of these groups.
$child_ids = array();
while ($row = $result->fetch_assoc()) {
    $id = $row['LobbyId'];
    $home[$id] = $row;
    $child_ids[] = $id;
}
if (sizeof($child_ids) == 0) {
    RenderErrorPage("No forum boards to display.");
    return;
}
$joined = implode(",", $child_ids);
sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId IN ($joined);", 1) or RenderErrorPage("No forum boards to display.");

// Init child lobby data.
while ($row = $result->fetch_assoc()) {
    $id = $row['LobbyId'];
    $pid = $row['ParentLobbyId'];
    if (in_array($id, $unread_boards)) {
        $row['unread'] = true;
    }
    $home[$pid]['childBoards'][$id] = $row;
}

// Set output.
GetBreadcrumbsFromBoardId($board_id, $names, $links) or RenderErrorPage("Board not found.");

$vars['home'] = $home;
$vars['crumbs'] = CreateCrumbsHTML($names, $links);
?>