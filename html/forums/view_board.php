<?php
// Views a board (list of boards or threads).
// URL: /forums/board/              => view_board.php?board=-1
// URL: /forums/board/{board-id}/   => view_board.php?board={board-id}
// URL: /forums/board/{board-name}/ => view_board.php?boardname={board-name}

include_once("../header.php");
include_once(SITE_ROOT."forums/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/listview.php");

$board_id = -1;
if (isset($_GET['board']) && is_numeric($_GET['board'])) {
    $board_id = (int)$_GET['board'];
} else if (isset($_GET['boardname'])) {
    $escaped_board_name = sql_escape($_GET['boardname']);
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE UPPER(Name)=UPPER('$escaped_board_name');", 1)) {
        $board_id = $result->fetch_assoc()['BoardId'];
    }
}

if ($board_id == -1) {
    $board = array();
    $board['BoardId'] = -1;
    InitBoardChildren($board);
    $vars['isRoot'] = true;
} else if (GetBoard($board_id, $board)) {
    $board_id = $board['BoardId'];  // Get db value.
    if (!(CanGuestViewBoard($board) || (isset($user) && CanUserViewBoard($user, $board)))) {
        RenderErrorPage("Board not found");  // Insufficient permissions.
    }
    if (sizeof($board['childBoards']) == 0) {
        // Fetch and display threads.
        $items_per_page = DEFAULT_FORUM_THREADS_PER_PAGE;  // TODO: Get user preferences here.
        $sort_order = GetThreadSortOrder();
        CollectItems(FORUMS_POST_TABLE, "WHERE ParentId=$board_id AND IsThread=1 ORDER BY $sort_order", $threads, $items_per_page, $iterator);
        InitPosters($threads);
        $vars['board'] = $board;
        $vars['threads'] = $threads;
        $vars['iterator'] = $iterator;
        if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
        if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];
        $vars['titleSortUrl'] = GetURLForSortOrder("title", "asc");
        $vars['repliesSortUrl'] = GetURLForSortOrder("replies", "desc");
        $vars['viewsSortUrl'] = GetURLForSortOrder("views", "desc");
        $vars['lastpostSortUrl'] = GetURLForSortOrder("lastpost", "desc");
        RenderPage("forums/view_board_threads.tpl");
        return;
    }
} else {
    RenderErrorPage("Board not found");
}
// Show lobby of boards.
$vars['board'] = $board;
RenderPage("forums/view_board_boards.tpl");
return;

function GetThreadSortOrder() {
    $order_clause = "EditDate DESC";
    if (isset($_GET['sort'])) {
        $order_asc = true;
        if (isset($_GET['order'])) {
            if (mb_strtolower($_GET['order']) == "asc") {
                $order_asc = true;
            } else if (mb_strtolower($_GET['order']) == "desc") {
                $order_asc = false;
            }
        }
        switch (mb_strtolower($_GET['sort'])) {
            case "title":
                $sort = "Title";
                break;
            case "replies":
                $sort = "Replies";
                break;
            case "views":
                $sort = "Views";
                break;
            case "lastpost":
                $sort = "LastPostDate";
                break;
            default:
                $sort = "EditDate";
                break;
        }
        $order = ($order_asc ? "ASC" : "DESC");
        $order_clause = "$sort $order";
    }
    return $order_clause;
}

function GetSortURL($board, $sort) {
    $base_sort_url = "/forums/board/".urlencode(mb_strtolower($board['Name']))."/?";
    foreach ($_GET as $key => $value) {
        $base_sort_url .= "$key=".urlencode($value)."&";
    }
    $base_sort_url .= "sort=".urlencode($sort);
    // Okay to not use multibyte string manipulation here.
    if (isset($_GET['sort']) && strtolower($_GET['sort']) == strtolower($sort)) {
        // Same sort type, reverse direction.
        if (isset($_GET['order']) && strtolower($_GET['order']) == "desc") {
            $base_sort_url .= "&order=asc";
        } else {
            $base_sort_url .= "&order=desc";
        }
    } else if (!isset($_GET['sort'])) {
        // Different sort type, use default descending order.
        $base_sort_url .= "&order=desc";
    }
    return $base_sort_url;
}

?>