<?php
// General library of functions used in the forums section.

include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/table_data.php");

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
    if ($user['ForumsPermissions'] == 'R') return false;
    if ($board['BoardId'] == -1) return false;  // Can't make threads under fake root board.
    if ($user['ForumsPermissions'] == 'A') return true;
    if (isset($board['childBoards']) && sizeof($board['childBoards']) > 0) return false;  // Can't post to top-level boards (Although admins can move posts to them).
    if ($board['PrivateBoard'] == 1) return false;
    if ($board['Locked'] == 1) return false;
    return true;
}
function CanUserPostToThread($user, $thread) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'R') return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($thread['Locked']) return false;
    // Don't check board locked status, as users can still reply to threads in locked boards.
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
    if ($user['ForumsPermissions'] == 'R') return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    if ($user['UserId'] == $post['UserId']) return true;
    return false;
}
function CanUserLockStickyOrMarkNewsThread($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    return false;
}
function CanUserMoveThread($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    return false;
}
function CanUserAdminBoard($user, $board) {  // Also for marking boards as admin-only private.
    if (!IsUserActivated($user)) return false;
    if ($user['ForumsPermissions'] == 'A') return true;
    return false;
}

// Fetches the board if it exists. Returns true if the board was found.
function GetBoard($board_id, &$ret_board) {
    // TODO: Reduce # of sql queries.
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
    // TODO: Reduce # of sql queries.
    global $user;
    $board_id = $board['BoardId'];
    $childBoards = array();
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE ParentId=$board_id ORDER BY BoardSortOrder ASC, BoardId ASC;", 1)) {
        while ($row = $result->fetch_assoc()) {
            if (CanGuestViewBoard($row) || (isset($user) && CanUserViewBoard($user, $row))) {
                $childBoards[] = $row;
            }
        }
        usort($childBoards, function($b1, $b2) {
            $order = $b1['BoardSortOrder'] - $b2['BoardSortOrder'];
            if ($order != 0) return $order;
            return strcmp($b1['Name'], $b2['Name']);
        });
        foreach ($childBoards as &$cb) {
            InitBoardChildren($cb);
        }
    }
    $board['childBoards'] = $childBoards;
}

function InitBoardParents(&$board) {
    // TODO: Reduce # of sql queries.
    $parent_id = $board['ParentId'];
    if ($parent_id != -1 && sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$parent_id;", 1)) {
        $parent = $result->fetch_assoc();
        InitBoardParents($parent);
        $board['parentBoard'] = $parent;
    }
}

function HasChildBoardId($board, $cbid) {
    if ($board['BoardId'] == $cbid) return true;
    if (isset($board['childBoards']) && sizeof($board['childBoards']) > 0) {
        foreach ($board['childBoards'] as $c) {
            if (HasChildBoardId($c, $cbid)) return true;
        }
    }
    return false;
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
    if (sizeof($posts) == 0) return true;
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
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE (PostId=$tid AND IsThread=1) OR (ParentId=$tid AND IsThread=0) ORDER BY PostDate ASC, PostId ASC;", 1)) return null;
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
    if (!sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE (ParentId=$tid AND IsThread=0);", 1)) return;
    $replies = $result->fetch_assoc()['C'];
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE (PostId=$tid AND IsThread=1) OR (ParentId=$tid AND IsThread=0) ORDER BY PostDate DESC, PostId DESC LIMIT 1;", 1)) return;
    $lastDate = $result->fetch_assoc()['PostDate'];
    sql_query("UPDATE ".FORUMS_POST_TABLE." SET Replies=$replies, LastPostDate=$lastDate WHERE PostId=$tid;");
}

function UpdateBoardStats($bid) {
    $numPosts = 0;
    $numThreads = 0;
    $lastPostId = -1;
    $lastPostDate = 0;
    // Get stats from threads.
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE IsThread=1 AND ParentId=$bid;", 1)) {
        $threads = array();
        while ($row = $result->fetch_assoc()) {
            $tid = $row['PostId'];
            $threads[] = $tid;
            $numThreads++;
        }
        $joined = implode(",", $threads);
        if (sizeof($threads) > 0 && sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE IsThread=0 AND ParentId IN ($joined);")) {
            $numPosts += $result->fetch_assoc()['C'];
        }
        $numPosts += $numThreads;
        if (sizeof($threads) > 0 && sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE (IsThread=1 AND PostId IN ($joined)) OR (IsThread=0 AND ParentId IN ($joined)) ORDER BY PostDate DESC LIMIT 1;", 1)) {
            $row = $result->fetch_assoc();
            $lastPostId = (int)$row['PostId'];
            $lastPostDate = (int)$row['PostDate'];
        }
    }
    // Get stats from boards.
    $currBoard = null;
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE BoardId=$bid OR ParentId=$bid;", 1)) {
        while ($row = $result->fetch_assoc()) {
            if ($row['BoardId'] == $bid) {
                $currBoard = $row;
            } else {
                $numPosts += $row['NumPosts'];
                $numThreads += $row['NumThreads'];
                // Use last post of child board if better.
                if ((int)$row['LastPostDate'] > $lastPostDate) {
                    $lastPostId = (int)$row['LastPostId'];
                    $lastPostDate = (int)$row['LastPostDate'];
                }
            }
        }
    }
    sql_query("UPDATE ".FORUMS_BOARD_TABLE." SET NumPosts=$numPosts, NumThreads=$numThreads, LastPostId=$lastPostId, LastPostDate=$lastPostDate WHERE BoardId=$bid;");
    // Update parent as well
    if ($currBoard != null) {
        UpdateBoardStats($currBoard['ParentId']);
    }
}

function FillBoardLastPostStats(&$board) {
    $lastPost = null;
    if (isset($board['childBoards'])) {
        foreach ($board['childBoards'] as &$cb) {
            FillBoardLastPostStats($cb);
            if ($board['BoardId'] != -1 && $cb['LastPostId'] == $board['LastPostId']) {
                $lastPost = $cb['lastPost'];
            }
        }
    }
    if ($lastPost == null && $board['BoardId'] != -1) {
        // Wasn't one of the child post stats, fetch it normally.
        $last_id = $board['LastPostId'];
        if ($last_id != -1) {
            $lastPost = GetLastPost($last_id);
        }
    }
    $board['lastPost'] = $lastPost;
}

function GetLastPost($pid) {
    $lastPost = null;
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$pid;", 1)) {
        $post = $result->fetch_assoc();
        // Get poster.
        $posts = array();
        $posts[] = &$post;
        InitPosters($posts);
        // Get formatted date.
        $post['date'] = FormatDate($post['PostDate'], FORUMS_DATE_FORMAT);
        // Get link.
        $tid = ($post['IsThread'] == 1 ? $post['PostId'] : $post['ParentId']);
        $posts_per_page = GetPostsPerPageInThread();
        if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".FORUMS_POST_TABLE." WHERE (IsThread=1 AND PostId=$tid) OR (IsThread=0 AND ParentId=$tid);", 1)) {
            // Only care about count, as last post is always the last one.
            $count = $result->fetch_assoc()['C'];
            $page = floor((int)($count + $posts_per_page - 1) / (int)$posts_per_page);
            $post['url'] = "/forums/thread/$tid/?page=$page#p$pid";
        }
        $lastPost = $post;
    }
    return $lastPost;
}

function GetLastPostInThread($tid) {
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE (IsThread=1 AND PostId=$tid) OR (IsThread=0 AND ParentId=$tid) ORDER BY PostDate DESC LIMIT 1;", 1)) {
        return GetLastPost($result->fetch_assoc()['PostId']);
    }
    return null;
}

function GetMixedUnreadPostIds($user) {
    $ret = array();
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_UNREAD_POST_TABLE." WHERE UserId=".$user['UserId'].";", 1)) {
        while ($row = $result->fetch_assoc()) {
            $pid = $row['PostId'];
            $ret[$pid] = $pid;
        }
    }
    return $ret;
}

function MarkPostsAsRead($user, $post_ids) {
    $uid = $user['UserId'];
    if (sizeof($post_ids) == 0) return;
    $post_ids = array_values($post_ids);
    sort($post_ids);
    $last_id = array_last($post_ids);
    $max_index = $user['MaybeReadUpTo'];
    $inserts = array();
    if ($max_index <= $last_id) {
        // Need to increment $max_index, and add a bunch of values to table.
        $new_max_index = $last_id + 1;
        sql_query("UPDATE ".FORUMS_USER_PREF_TABLE." SET MaybeReadUpTo=$new_max_index WHERE UserId=$uid;");
        // Also update unread post table.
        $joined_post_ids = implode(",", $post_ids);
        sql_query("INSERT INTO ".FORUMS_UNREAD_POST_TABLE."
            SELECT $uid, PostId FROM ".FORUMS_POST_TABLE."
            WHERE $max_index <= PostId
                AND PostId < $new_max_index
                AND PostId NOT IN ($joined_post_ids);");
    }
    // Now, update all indices < $max_index.
    $delete_joined = implode(",", $post_ids);
    sql_query("DELETE FROM ".FORUMS_UNREAD_POST_TABLE." WHERE UserId=$uid AND PostId IN ($delete_joined);");
}

function MarkAllAsRead($user) {
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." ORDER BY PostId DESC LIMIT 1;", 1)) {
        $uid = $user['UserId'];
        $pid = $result->fetch_assoc()['PostId'];
        sql_query("DELETE FROM ".FORUMS_UNREAD_POST_TABLE." WHERE UserId=$uid;");
        sql_query("UPDATE ".FORUMS_USER_PREF_TABLE." SET MaybeReadUpTo=".($pid + 1)." WHERE UserId=$uid;");
    }
}

function TagThreadsAsUnread($user, &$thread_posts) {
    $uid = $user['UserId'];
    $tids = array_map(function($thread) { return $thread['PostId']; }, $thread_posts);
    $joined_tids = implode(",", $tids);
    $maybe_read_up_to = $user['MaybeReadUpTo'];
    if (!sql_query_into($result,
        "SELECT * FROM ".FORUMS_POST_TABLE." T WHERE
            IsThread=1 AND PostId IN ($joined_tids) AND
            EXISTS(SELECT 1 FROM ".FORUMS_POST_TABLE." S WHERE
                (S.PostId=T.PostId OR (S.ParentId=T.PostId AND S.IsThread=0)) AND
                (S.PostId >= $maybe_read_up_to OR EXISTS(SELECT 1 FROM ".FORUMS_UNREAD_POST_TABLE." U WHERE U.UserId=$uid AND U.PostId=S.PostId)));", 1)) return;
    $unread_tids = array();
    while ($row = $result->fetch_assoc()) {
        $tid = $row['PostId'];
        $unread_tids[$tid] = true;
    }
    foreach ($thread_posts as &$thread) {
        $thread['unread'] = isset($unread_tids[$thread['PostId']]);
        $tid = $thread['PostId'];
        // Find oldest unread post.
        if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." S WHERE
            (S.PostId=$tid OR (S.ParentId=$tid AND S.IsThread=0)) AND
            (S.PostId >= $maybe_read_up_to OR EXISTS(SELECT 1 FROM ".FORUMS_UNREAD_POST_TABLE." U WHERE U.UserId=$uid AND U.PostId=S.PostId))
            ORDER BY PostDate ASC LIMIT 1;", 1)) {
            $first_unread_post = $result->fetch_assoc();
            $upid = $first_unread_post['PostId'];
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." S WHERE
                (S.PostId=$tid OR (S.ParentId=$tid AND S.IsThread=0)) ORDER BY PostDate ASC;", 1)) {
                $index = 0;
                $post_index = -1;
                while ($row = $result->fetch_assoc()) {
                    if ($row['PostId'] == $upid) {
                        $post_index = $index;
                        break;
                    }
                    $index++;
                }
                if ($post_index >= 0) {
                    $posts_per_page = GetPostsPerPageInThread();
                    $page = ((int)($index / $posts_per_page)) + 1;
                    $thread['first_unread_url'] = "/forums/thread/$tid/?page=$page#p$upid";
                }
            }
        }
    }
}

function TagBoardsAsUnread($user, &$board) {
    $uid = $user['UserId'];
    $maybe_read_up_to = $user['MaybeReadUpTo'];
    if (!sql_query_into($result,
        "SELECT ParentId FROM ".FORUMS_POST_TABLE." T WHERE
            IsThread=1 AND
            EXISTS(SELECT 1 FROM ".FORUMS_POST_TABLE." S WHERE
                (S.PostId=T.PostId OR (S.ParentId=T.PostId AND S.IsThread=0)) AND
                (S.PostId >= $maybe_read_up_to OR EXISTS(SELECT 1 FROM ".FORUMS_UNREAD_POST_TABLE." U WHERE U.UserId=$uid AND U.PostId=S.PostId)));", 1)) return;
    $unread_board_ids = array();
    while ($row = $result->fetch_assoc()) {
        $bid = $row['ParentId'];
        $unread_board_ids[$bid] = true;
    }
    $MarkBoardRecursive = function (&$board) use ($unread_board_ids, &$MarkBoardRecursive) {
        $bid = $board['BoardId'];
        $has_unread = false;
        if (isset($unread_board_ids[$bid])) $has_unread = true;
        if (isset($board['childBoards'])) {
            foreach ($board['childBoards'] as &$b) {
                if ($MarkBoardRecursive($b)) {
                    $has_unread = true;
                }
            }
        }
        if ($has_unread) {
            $board['unread'] = true;
        }
        return $has_unread;
    };
    $MarkBoardRecursive($board);
}

function GetOrderedBoardTree($include_root=false) {
    $root = array(
        "Name" => "Root",
        "BoardId" => -1
    );
    InitBoardChildren($root);
    $ret = array();
    RecursivelyFillBoardList($ret, $root, ($include_root ? 0 : -1));
    return $ret;
}

function RecursivelyFillBoardList(&$list, $board, $depth) {
    global $user;
    if (CanUserViewBoard($user, $board)) {
        if ($depth >= 0) {
            $board['depth'] = $depth;
            $list[] = $board;
        }
        foreach ($board['childBoards'] as $cb) {
            RecursivelyFillBoardList($list, $cb, $depth + 1);
        }
    }
}
?>