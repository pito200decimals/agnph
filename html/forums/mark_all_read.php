<?php
// Page to mark all posts on the given board as read. Redirects to the forums index afterwards.
// URL: /forums/markallread/{board-id}
// URL: /forums/mark_all_read.php?b={board-id}

// Site includes, including login authentication.
include_once("../header.php");
include_once(__DIR__."/includes/functions.php");

if (!isset($user)) {
    header("Location: /forums/");
    return;
}
if (!isset($_GET['b']) || !is_numeric($_GET['b'])) {
    RenderErrorPage("Invalid URL.");
    return;
}

$board_id = $_GET['b'];
$escaped_board_id = sql_escape($board_id);
sql_query_into($result, "SELECT PostId FROM ".FORUMS_POST_TABLE." WHERE ParentLobbyId='$escaped_board_id';", 0) or RenderErrorPage("Error while marking items as read.");
$thread_ids = array();
while ($row = $result->fetch_assoc()) {
    $thread_ids[] = $row['PostId'];
}
if (sizeof($thread_ids) > 0) {
    $joined = "(".implode(",", $thread_ids).")";
    sql_query_into($result, "SELECT PostId FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId IN $joined OR PostId IN $joined;", 1) or RenderErrorPage("Error while marking items as read.");
    $post_ids = array();
    while ($row = $result->fetch_assoc()) {
        $post_ids[] = $row['PostId'];
    }
    MarkPostsAsRead($user, $post_ids);
}
header("Location: /forums/board/$board_id/");
return;

?>