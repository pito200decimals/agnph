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
    FillBoardLastPostStats($board);
    if (isset($user)) TagBoardsAsUnread($user, $board);
    HandlePost($board);
} else if (GetBoard($board_id, $board)) {  // Also gets children.
    HandlePost($board);
    $board_id = $board['BoardId'];  // Get db value.
    if (!(CanGuestViewBoard($board) || (isset($user) && CanUserViewBoard($user, $board)))) {
        RenderErrorPage("Board not found");  // Insufficient permissions.
    }
    FillBoardLastPostStats($board);
    if (isset($user)) TagBoardsAsUnread($user, $board);
    // Fetch and display threads.
    $items_per_page = GetThreadsPerPageInBoard();
    $sort_order = GetThreadSortOrder();
    CollectItems(FORUMS_POST_TABLE, "WHERE ParentId=$board_id AND IsThread=1 ORDER BY Sticky DESC, $sort_order", $threads, $items_per_page, $iterator);
    InitPosters($threads);
    if (isset($user)) TagThreadsAsUnread($user, $threads);
    foreach ($threads as &$thread) {
        $thread['lastPost'] = GetLastPostInThread($thread['PostId']);
    }
    $vars['threads'] = $threads;
    $vars['iterator'] = $iterator;
    if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
    if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];
    $vars['titleSortUrl'] = GetURLForSortOrder("title", "asc");
    $vars['repliesSortUrl'] = GetURLForSortOrder("replies", "desc");
    $vars['viewsSortUrl'] = GetURLForSortOrder("views", "desc");
    $vars['lastpostSortUrl'] = GetURLForSortOrder("lastpost", "desc");
} else {
    RenderErrorPage("Board not found");
}
if (isset($user)) {
    // Set up permissions.
    $vars['canCreateThread'] = CanUserCreateThread($user, $board);
    $vars['canAdminBoard'] = CanUserAdminBoard($user, $board);
    if ($vars['canAdminBoard']) {
        $vars['allBoards'] = GetOrderedBoardTree(true);
    }
}
// Show lobby of boards.
$vars['board'] = $board;
if (sizeof($board['childBoards']) == 0) {
    RenderPage("forums/view_board_threads.tpl");
} else {
    RenderPage("forums/view_board_boards.tpl");
}
return;

function GetBoardStats(&$boardTree) {

}

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

function HandlePost($board) {
    if (!CanPerformSitePost()) MaintenanceError();
    global $user;
    if (!isset($_POST['action'])) return;
    $action = $_POST['action'];
    if (!isset($user)) return;
    if (!CanUserAdminBoard($user, $board)) {
        RenderErrorPage("Not authorized to perform this action");
    }
    $bid = $board['BoardId'];
    // TODO: Decide if logging is desired here.
    switch ($action) {
        case "lock":
            if ($board['BoardId'] == -1) break;
            sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET Locked=1 WHERE BoardId=$bid;");
            PostSessionBanner("Board locked", "green");
            break;
        case "unlock":
            if ($board['BoardId'] == -1) break;
            sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET Locked=0 WHERE BoardId=$bid;");
            PostSessionBanner("Board unlocked", "green");
            break;
        case "mark-private":
            if ($board['BoardId'] == -1) break;
            sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET PrivateBoard=1 WHERE BoardId=$bid;");
            PostSessionBanner("Board marked private", "green");
            break;
        case "mark-public":
            if ($board['BoardId'] == -1) break;
            sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET PrivateBoard=0 WHERE BoardId=$bid;");
            PostSessionBanner("Board marked public", "green");
            break;
        case "move-board":
            if ($board['BoardId'] == -1) break;
            $pid = $_POST['parent-board'];
            $success = true;
            if ($pid == -1) {
                // Leave as root.
            } else if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$pid;", 1)) {
                $pid = $result->fetch_assoc()['BoardId'];
            } else {
                PostSessionBanner("Error moving board", "red");
                break;
            }
            // Re-compute old placement.
            $old_pid = $board['ParentId'];
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$old_pid ORDER BY BoardSortOrder ASC;", 1)) {
                $new_id_order_mapping = array();
                $index = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($row['BoardId'] == $bid) continue;
                    $new_id_order_mapping[$row['BoardId']] = $index;
                    $index++;
                }
            } else {
                PostSessionBanner("Error moving board", "red");
                break;
            }
            // Compute new placement.
            if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$pid;", 1)) {
                $index = $result->fetch_assoc()['C'];  // Place at end.
            } else {
                PostSessionBanner("Error moving board", "red");
                break;
            }
            // Apply both order updates at the same time.
            sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET ParentId=$pid, BoardSortOrder=$index WHERE BoardId=$bid;");
            foreach ($new_id_order_mapping as $id => $order) {
                sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET BoardSortOrder=$order WHERE BoardId=$id;");
            }
            PostSessionBanner("Board moved", "green");
            break;
        case "create":
            if (!isset($_POST['name']) || !isset($_POST['description'])) {
                PostSessionBanner("Error creating board", "red");
                break;
            }
            $name = $_POST['name'];
            $description = $_POST['description'];
            // Only allow alpha-numeric and spaces.
            if (!preg_match("/^[A-Za-z0-9 ]+$/", $name)) {
                PostSessionBanner("Error creating board", "red");
                break;
            }
            // Compute new placement.
            if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$bid;", 1)) {
                $index = $result->fetch_assoc()['C'];  // Place at end.
            } else {
                PostSessionBanner("Error creating board", "red");
                break;
            }
            $escaped_name = sql_escape($name);
            $escaped_description = sql_escape($description);
            if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_BOARD_TABLE." WHERE Name='$escaped_name';", 1)) {
                if ($result->fetch_assoc()['C'] > 0) {
                    PostSessionBanner("Board name already exists", "red");
                    break;
                }
            } else {
                PostSessionBanner("Error creating board", "red");
                break;
            }
            sql_query("INSERT INTO ".FORUMS_BOARD_TABLE." (ParentId, Name, Description, BoardSortOrder) VALUES ($bid, '$escaped_name', '$escaped_description', $index);");
            // Manually redirect to new board.
            $final_url = "/forums/board/".urlencode($name)."/";
            header("Location: $final_url");
            exit();
        case "delete":
            if ($board['BoardId'] == -1) break;
            if (!sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$bid;", 1)) {
                PostSessionBanner("Error deleting board", "red");
                break;
            }
            if ($result->fetch_assoc()['C'] > 0) {
                PostSessionBanner("Board must contain no children before deleting", "red");
                break;
            }
            if (!sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE IsThread=1 AND ParentId=$bid;", 1)) {
                PostSessionBanner("Error deleting board", "red");
                break;
            }
            if ($result->fetch_assoc()['C'] > 0) {
                PostSessionBanner("Board must contain no threads before deleting", "red");
                break;
            }
            $pid = $board['ParentId'];
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$pid ORDER BY BoardSortOrder ASC;", 1)) {
                $index = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($row['BoardId'] == $bid) continue;
                    sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET BoardSortOrder=$index WHERE BoardId=".$row['BoardId'].";");
                    $index++;
                }
            } else {
                PostSessionBanner("Error deleting board", "red");
                break;
            }
            sql_query("DELETE FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$bid;");
            // Manually redirect to parent board.
            if ($pid == -1) {
                $final_url = "/forums/board/";
            } else if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$pid;", 1)) {
                $parentName = $result->fetch_assoc()['Name'];
                $final_url = "/forums/board/".urlencode($parentName)."/";
            }
            header("Location: $final_url");
            exit();
        case "rename":
            if ($board['BoardId'] == -1) break;
            if (!isset($_POST['name']) || !isset($_POST['description'])) {
                PostSessionBanner("Error renaming board", "red");
                break;
            }
            $name = $_POST['name'];
            $description = $_POST['description'];
            // Only allow alpha-numeric and spaces.
            if (!preg_match("/^[A-Za-z0-9 ]+$/", $name)) {
                PostSessionBanner("Error renaming board", "red");
                break;
            }
            $escaped_name = sql_escape($name);
            if ($description == "") {
                sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET Name='$escaped_name' WHERE BoardId=$bid;");
            } else {
                $escaped_description = sql_escape($description);
                sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET Name='$escaped_name', Description='$escaped_description' WHERE BoardId=$bid;");
            }
            // Manually redirect to new board name.
            $final_url = "/forums/board/".urlencode($name)."/";
            header("Location: $final_url");
            exit();
        default:
            // Not a valid POST.
            return;
    }
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

?>