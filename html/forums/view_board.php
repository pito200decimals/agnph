<?php
// Views a board (list of boards or threads).
// URL: /forums/board/              => view_board.php?board=-1
// URL: /forums/board/{board-id}/   => view_board.php?board={board-id}
// URL: /forums/board/{board-name}/ => view_board.php?boardname={board-name}

define("PRETTY_PAGE_NAME", "Forums");

include_once("../header.php");
include_once(SITE_ROOT."forums/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/listview.php");

$board_id = -1;
if (isset($_GET['board']) && is_numeric($_GET['board'])) {
    $board_id = (int)$_GET['board'];
} else if (isset($_GET['boardname'])) {
    $escaped_board_name = sql_escape(urldecode($_GET['boardname']));
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
    $sort_order = GetQueryOrder();
    CollectItems(FORUMS_POST_TABLE, "WHERE ParentId=$board_id AND IsThread=1 ORDER BY Sticky DESC, $sort_order", $threads, $items_per_page, $iterator, "Error accessing board");
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

function GetQueryOrder() {
    $result = GetSortClausesList(function($key, $order_asc) {
        $order = ($order_asc ? "ASC" : "DESC");
        switch ($key) {
            case "title":
                return "Title $order";
                break;
            case "replies":
                return "Replies $order";
            case "views":
                return "Views $order";
            case "lastpost":
                return "LastPostDate $order";
        }
        return null;
    });
    $result[] = "LastPostDate DESC";
    return implode(", ", $result);
}

function HandlePost($board) {
    global $user;
    if (!isset($_POST['action'])) return;
    $action = $_POST['action'];
    if (!isset($user)) return;
    if (!CanPerformSitePost()) MaintenanceError();
    if (!CanUserAdminBoard($user, $board)) {
        RenderErrorPage("Not authorized to perform this action");
    }
    $bid = $board['BoardId'];
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
            if (HasChildBoardId($board, $pid)) {
                // Board can't become its own child.
                PostSessionBanner("Can't move board to be its own child.", "red");
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
            UpdateBoardStats($pid);
            UpdateBoardStats($old_pid);
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
            $escaped_name = sql_escape(GetSanitizedTextTruncated($name, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_BOARD_TITLE_LENGTH));
            $escaped_description = sql_escape(GetSanitizedTextTruncated($description, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_BOARD_DESCRIPTION_LENGTH));
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
            Redirect("$final_url");
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
            UpdateBoardStats($pid);
            Redirect("$final_url");
            return;
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
            $escaped_name = sql_escape(GetSanitizedTextTruncated($name, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_BOARD_TITLE_LENGTH));
            if ($description == "") {
                sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET Name='$escaped_name' WHERE BoardId=$bid;");
            } else {
                $escaped_description = sql_escape(GetSanitizedTextTruncated($description, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_BOARD_DESCRIPTION_LENGTH));
                sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET Name='$escaped_name', Description='$escaped_description' WHERE BoardId=$bid;");
            }
            // Manually redirect to new board name.
            $final_url = "/forums/board/".urlencode(mb_strtolower($name, "UTF-8"))."/";
            Redirect("$final_url");
            return;
        default:
            // Not a valid POST.
            return;
    }
    Redirect($_SERVER['HTTP_REFERER']);
}

?>