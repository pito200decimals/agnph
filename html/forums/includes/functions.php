<?php
// General library of functions used in the forums section.

include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/table_data.php");

// TODO: Overall, permissions for restricted users.
function CanGuestViewBoard($board) {
    if ($board['PrivateBoard'] == 1) return false;
    return true;
}
function CanUserViewBoard($user, $board) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($board['PrivateBoard'] == 1) return false;
    return true;
}
function CanUserCreateThread($user, $board) {
    if (!IsUserActivated($user)) return false;
    if (isset($board['childBoards']) && sizeof($board['childBoards']) > 0) return false;  // Can't post to top-level boards.
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($board['PrivateBoard'] == 1) return false;
    if ($board['Locked'] == 1) return false;
    return true;
}
function CanUserPostToThread($user, $thread) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($thread['Locked']) return false;
    return true;
}
function CanUserEditForumsPost($user, $thread, $post) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($user['UserId'] == $post['UserId']) return true;
    return false;
}
function CanUserDeleteForumsPost($user, $thread, $post) {
    if ($post['IsThread'] == 1 && sizeof($thread['posts']) > 1) return false;
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($user['UserId'] == $post['UserId']) return true;
    return false;
}
function CanUserLockOrStickyThread($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    return false;
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
        'Sticky' => $row['Sticky'],
        'Locked' => $row['Locked']);
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

function GetPostsInThread($thread_or_tid) {
    if (is_array($thread_or_tid)) return GetPostsInThread($thread_or_tid['ThreadId']);
    $tid = (int)$thread_or_tid;
    // TODO: Remove order by IsThread.
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE (PostId=$tid AND IsThread=1) OR (ParentId=$tid AND IsThread=0) ORDER BY IsThread DESC, PostDate ASC, PostId ASC;", 1)) return null;
    $ret = array();
    while ($row = $result->fetch_assoc()) {
        $ret[] = $row;
    }
    return $ret;
}

function GetThreadsPerPageInBoard() {
    global $user;
    if (isset($user)) {
        return $user['ForumThreadsPerPage'];
    }
    return DEFAULT_FORUM_THREADS_PER_PAGE;
}

function GetPostsPerPageInThread() {
    global $user;
    if (isset($user)) {
        return $user['ForumPostsPerPage'];
    }
    return DEFAULT_FORUM_POSTS_PER_PAGE;
}

function UpdateThreadStats($tid) {
    // Update # of replies, and last post date.
    if (!sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE (PostId=$tid AND IsThread=1) OR (ParentId=$tid AND IsThread=0);", 1)) return;
    $replies = $result->fetch_assoc()['C'];
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE (PostId=$tid AND IsThread=1) OR (ParentId=$tid AND IsThread=0) ORDER BY PostDate DESC, PostId DESC LIMIT 1;", 1)) return;
    $lastDate = $result->fetch_assoc()['PostDate'];
    sql_query("UPDATE ".FORUMS_POST_TABLE." SET Replies=$replies, LastPostDate=$lastDate WHERE PostId=$tid;");
}

function UpdateBoardStats($bid) {
    // TODO, when stats are supported for boards.
}


?>