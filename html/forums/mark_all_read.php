<?php
// Marks all threads as read.
// URL: /forums/mark-all-read/ => mark_all_read.php?[board={board-id}]

include_once("../header.php");
include_once(SITE_ROOT."forums/includes/functions.php");

if (!isset($user)) {
    InvalidURL();
}

if (isset($_GET['board']) && is_numeric($_GET['board'])) {
    // Mark only posts in a board as read.
    $bid = (int)$_GET['board'];
    if (GetBoard($bid, $board)) {
        $ids = array();
        GetAllBoardIds($board, $ids);
        $joined = implode(",", $ids);  // $ids is always > 0.
        if (sql_query_into($result,
            "SELECT * FROM ".FORUMS_POST_TABLE." T WHERE
                (IsThread=1 AND ParentId IN ($joined)) OR
                (IsThread=0 AND EXISTS(SELECT 1 FROM ".FORUMS_POST_TABLE." S WHERE
                        S.PostId=T.ParentId AND
                        S.IsThread AND
                        S.ParentId IN ($joined)));", 1)) {
            $pids = array();
            while ($row = $result->fetch_assoc()) {
                $pids[] = $row['PostId'];
            }
            MarkPostsAsRead($user, $pids);
            PostSessionBanner("Posts marked as read", "green");
        }
    } else {
        PostSessionBanner("Error marking posts as read", "red");
    }
} else {
    // Mark all posts as read.
    MarkAllAsRead($user);
    PostSessionBanner("Posts marked as read", "green");
}
// Go back to requesting page.
Redirect($_SERVER['HTTP_REFERER']);

function GetAllBoardIds($board, &$ret) {
    $ret[] = $board['BoardId'];
    if (isset($board['childBoards'])) {
        foreach ($board['childBoards'] as $cb) {
            GetAllBoardIds($cb, $ret);
        }
    }
}

?>