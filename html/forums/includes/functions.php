<?php
// General library of functions used in the forums section.

include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/table_data.php");

function CanGuestViewBoard($board) {
    return true;
}
function CanUserViewBoard($user, $board) {
    return true;
}
function CanUserCreateThread($user, $lobby) {
    return true;
}
function CanUserPostToThread($user, $thread) {
    return true;
}
function CanUserEditForumsPost($user, $thread, $post) {
    return $user['UserId'] == $post['user']['UserId'];
}
function CanUserDeleteForumsPost($user, $thread, $post) {
    if ($post['IsThread'] == 1 && sizeof($thread['posts']) > 1) return false;
    return $user['UserId'] == $post['user']['UserId'];
}
function CanUserStickyThread($user, $board, $thread) {
    return true;
}

// Fetches the board if it exists. Returns true if the board was found.
function GetBoard($board_id, &$ret_board) {
    global $user;
    $escaped_board_id = sql_escape($board_id);
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId='$escaped_board_id';", 1)) {
        $board = $result->fetch_assoc();
        if (CanGuestViewBoard($board) || (isset($user) && CanUserViewBoard($user, $board))) {
            InitBoardChildren($board);
            InitBoardParents($board);
            $ret_board = $board;
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function InitBoardChildren(&$board) {
    global $user;
    $board_id = $board['BoardId'];
    $childBoards = array();
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$board_id ORDER BY BoardSortOrder ASC, BoardId ASC;", 1)) {
        while ($row = $result->fetch_assoc()) {
            if (CanGuestViewBoard($row) || (isset($user) && CanUserViewBoard($user, $row))) {
                InitBoardChildren($row);
                $childBoards[] = $row;
            }
        }
    }
    $board['childBoards'] = $childBoards;
}
function InitBoardParents(&$board) {
    $parent_id = $board['ParentId'];
    if ($parent_id != -1 && sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$parent_id;", 1)) {
        $parent = $result->fetch_assoc();
        InitBoardParents($parent);
        $board['parentBoard'] = $parent;
    }
}

function FetchThread($thread_id) {
    $escaped_thread_id = sql_escape($thread_id);
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_thread_id' AND IsThread=1;", 1)) return null;
    $row = $result->fetch_assoc();
    $thread = array(
        'ThreadId' => $row['PostId'],
        //'Poster' => GetUser($row['UserId']),
        'Title' => $row['Title'],
        'PostDate' => $row['PostDate'],
        'ParentBoardId' => $row['ParentId'],
        'Replies' => $row['Replies'],
        'Views' => $row['Views'],
        'LastPostDate' => $row['LastPostDate'],
        'Sticky' => $row['Sticky']);
    return $thread;
}

function InitPosters(&$posts) {
    $user_ids = array();
    foreach ($posts as $post) {
        $user_ids[] = $post['UserId'];
    }
    $user_ids = array_values(array_unique($user_ids));
    if (LoadTableData(array(USER_TABLE, FORUMS_USER_PREF_TABLE), "UserId", $user_ids, $users_by_id)) {
        foreach ($users_by_id as &$usr) {
            $usr['avatarURL'] = GetAvatarURL($usr);
        }
        foreach ($posts as &$post) {
            $post['user'] = $users_by_id[$post['UserId']];
        }
        return true;
    }
    return false;
}

?>