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

// Default content.
$vars['content'] = "No forum boards to display.";

if ($board_id == -1) {
    include(__DIR__."/viewboard-root.php");
    // Render page template.
    RenderPage("forums/viewboard-root.tpl");
    return;
} else {
    include(__DIR__."/viewboard-board.php");
    // Render page template.
    RenderPage("forums/viewboard-board.tpl");
    return;
}


?>