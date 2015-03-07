<?php
// Views the forum home page. Included from viewboard.php.

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
$joined = implode(",", $child_ids);
sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE ParentLobbyId IN ($joined);", 1) or RenderErrorPage("No forum boards to display.");

// Init child lobby data.
while ($row = $result->fetch_assoc()) {
    $id = $row['LobbyId'];
    $pid = $row['ParentLobbyId'];
    $home[$pid]['childBoards'][$id] = $row;
}

// Set output.
GetBreadcrumbsFromBoardId($board_id, $names, $links) or RenderErrorPage("Board not found.");
$vars['home'] = $home;
$vars['crumbs'] = CreateCrumbsHTML($names, $links);
?>