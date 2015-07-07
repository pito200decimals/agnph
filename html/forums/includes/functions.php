<?php
// General library of functions used in the forums section.

include_once(SITE_ROOT."includes/util/user.php");

// Permissions functions
function CanUserEditForumsPost($user, $post) {
    if (!isset($user)) debug_die("Unexpected unset user var", __FILE__, __LINE__);
    if (!isset($post)) debug_die("Unexpected unset post var", __FILE__, __LINE__);
    return isset($post) && ($user['UserId'] == $post['UserId'] || $user['ForumsPermissions'] == "A");
}
function CanUserDeleteForumsPost($user, $post) {
    if (!isset($user)) debug_die("Unexpected unset user var", __FILE__, __LINE__);
    if (!isset($post)) debug_die("Unexpected unset post var", __FILE__, __LINE__);
    return CanUserEditForumsPost($user, $post);
}
function CanUserPostToBoard($user, $board_id) {
    if (!isset($user)) debug_die("Unexpected unset user var", __FILE__, __LINE__);
    return true;
}
function CanUserPostToThread($user, $thread_post) {
    if (!isset($user)) debug_die("Unexpected unset user var", __FILE__, __LINE__);
    return true;
}
function CanUserStickyThread($user, $board_id) {
    if (!isset($user)) debug_die("Unexpected unset user var", __FILE__, __LINE__);
    return $user['ForumsPermissions'] == "A";
}

// Gets all posts in the given thread id. Returns the array of posts.
function GetAllPostsInThread($tid) {
    $escaped_tid = sql_escape($tid);
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId='$escaped_tid' OR PostId='$escaped_tid' ORDER BY PostId;", 1)) {
        $posts = array();
        while ($row = $result->fetch_assoc()) {
            $posts[$row['PostId']] = $row;
        }
        return $posts;
    } else {
        return null;
    }
}

// Gets all user data for the given list of posts. Updates the array of posts with poster data.
// Returns true on success, false on failure.
function GetAllPosterData(&$posts) {
    global $user;
    $uids = array();
    foreach ($posts as $post) {
        $uids[] = $post['UserId'];
    }
    $users = array();
    if (LoadTableData(array(USER_TABLE, FORUMS_USER_PREF_TABLE), "UserId", $uids, $users)) {
        foreach ($users as &$usr) {
            $usr['avatarURL'] = GetAvatarURL($usr);
            $usr['Signature'] = SanitizeHTMLTags($usr['Signature'], DEFAULT_ALLOWED_TAGS);
        }
        foreach ($posts as &$post) {
            $post['poster'] = $users[$post['UserId']];
            $post['PostDate'] = FormatDate($post['PostDate']);
            if ($post['EditDate'] != 0) {
                $post['EditDate'] = FormatDate($post['EditDate']);
            } else {
                // Don't display EditDate.
                unset($post['EditDate']);
            }
        }
        return true;
    } else {
        return false;
    }
}

// Gets all threads in a lobby. Returns the list of threads, or null if there is an error.
function GetAllThreadsInLobby($board) {
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentLobbyId=$board ORDER BY Sticky DESC, PostId DESC;", 0)) {
        $threads = array();
        while ($row = $result->fetch_assoc()) {
            $thread = $row;
            $tid = $thread['PostId'];
            $thread['PostDate'] = FormatDate($thread['PostDate']);
            $threads[$tid] = $thread;
        }
        return $threads;
    } else {
        return null;
    }
}

// Updates the creator field of all threads in the argument array. Returns true on success, false on failure.
function GetAllThreadCreatorData(&$threads) {
    $user_ids = array();
    foreach ($threads as $thread) {
        $user_ids[] = $thread['UserId'];
    }
    $users = array();
    if (LoadTableData(array(USER_TABLE), "UserId", $user_ids, $users)) {
        foreach ($threads as &$thread) {
            $thread['creator'] = $users[$thread['UserId']];
        }
        return true;
    } else {
        return false;
    }
}

// Initializes the post action links.
function SetPostLinks(&$post, $compose) {
    global $user;
    if (is_array($post) && !isset($post['PostId'])) {
        foreach ($post as &$p) {
            SetPostLinks($p, $compose);
        }
        return;
    }
    // TODO: Add user actions like quote and delete.
    if (isset($user)) {
        // All user actions.
        $pid = $post['PostId'];
        $tid = $post['ParentThreadId'];
        if ($compose) {
            // Run scripts to load the text into the compose box.
            //$post['quoteLink'] = "href='#quote' onclick='quote(\"p$pid\")'";
        } else {
            //$post['quoteLink'] = "/forums/reply/$tid/?quote=$pid";
        }
        // TODO: Add more actions on forum posts.
        if (CanUserEditForumsPost($user, $post)) $post['modifyLink'] = true;
        if (CanUserDeleteForumsPost($user, $post)) $post['deleteLink'] = true;
    }
}

function GetBreadcrumbsFromBoardId($board_id, &$names, &$links) {
    $names = array();
    $links = array();
    while ($board_id != -1) {
        if (sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId='$board_id';", 1)) {
            $lobby = $result->fetch_assoc();
            $names[] = $lobby['Name'];
            $links[] = "/forums/board/$board_id/";
            $board_id = $lobby['ParentLobbyId'];
        } else {
            return false;
        }
    }
    $names[] = "Forums";
    $links[] = "/forums/";
    $names = array_reverse($names);
    $links = array_reverse($links);
    return true;
}

// Same as GetBreadcrumbs, but takes a post id.
function GetBreadcrumbsFromPostId($post_id, &$names, &$links) {
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$post_id';", 1)) {
        $post = $result->fetch_assoc();
        return GetBreadcrumbsFromPost($post, $names, $links);
    } else {
        return false;
    }
}

// Gets the list of breadcrumbs from the forums home to the post in question.
// Home > Lobby Group > Board > Thread Name.
// Returns true on success, false on failure.
function GetBreadcrumbsFromPost($post, &$names, &$links) {
    if ($post['ParentThreadId'] == -1) {
        // Is a root post.
        if ($post['ParentLobbyId'] != -1) {
            if (GetBreadcrumbsFromBoardId($post['ParentLobbyId'], $names, $links)) {
                $names[] = $post['Title'];
                $pid = $post['PostId'];
                $links[] = "/forums/thread/$pid/";
                return true;
            } else {
                return false;
            }
        } else {
            // This shouldn't happen!
            debug("Found a post with ParentThreadId and ParentLobbyId as -1");
            debug_die($post);
            return false;
        }
    } else {
        // Is not a root post. Search now for a root post.
        // (Can only recurse 1 level deep).
        return GetBreadcrumbsFromPostId($post['ParentThreadId'], $names, $links);
    }
}

function CreateCrumbsHTML($names, $links) {
    $html = array_map(
        function($name, $link){
            return "<a href='$link' style='margin:3px;'>$name</a>";
        }, $names, $links);
    return "<ul><li>".implode(" » </li><li>", $html)."</li></ul>";
}

// Returns any unread items of the given user.
// Returns true on success, false on failure.
function GetUnreadPostIds($user, &$posts, &$threads, &$boards) {
    $id_read_up_to = $user['SeenPostsUpToId'];
    $extra_ids = array();
    $uid = $user['UserId'];
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_UNREAD_POST_TABLE." WHERE UserId=$uid;", 0)) return false;
    while ($row = $result->fetch_assoc()) {
        $extra_ids[] = $row['PostId'];
    }
    $extra_sql = "";
    if (sizeof($extra_ids) > 0) {
        $extra_sql = " OR PostId IN (".implode(",", $extra_ids).")";
    }
    if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId>$id_read_up_to".$extra_sql.";", 0)) return false;
    $thread_ids = array();
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row['PostId'];
        if ($row['ParentThreadId'] != -1) {
            $thread_ids[] = $row['ParentThreadId'];
        } else {
            $thread_ids[] = $row['PostId'];
        }
    }
    $thread_ids = array_unique($thread_ids);
    if (sizeof($thread_ids) > 0) {
        if (!sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId IN (".implode(",", $thread_ids).");", 0)) return false;
        $board_ids = array();
        while ($row = $result->fetch_assoc()) {
            $threads[] = $row['PostId'];
            if ($row['ParentLobbyId'] != -1) {
                $board_ids[] = $row['ParentLobbyId'];
            } else {
                debug_die("Expected root post to have a ParentLobbyId");
            }
        }
        $board_ids = array_unique($board_ids);
        if (!sql_query_into($result, "SELECT * FROM ".FORUMS_LOBBY_TABLE." WHERE LobbyId IN (".implode(",", $board_ids).");", 0)) return false;
        while ($row = $result->fetch_assoc()) {
            $boards[] = $row['LobbyId'];
        }
    }
    return true;
}

// Marks the given post id as read. Should be called in reverse-id order, if possible.
// If this function fails, we can't really recover easily. Print debug info and do no error handling.
function MarkPostsAsRead(&$user, $post_ids) {
    $max_id = max($post_ids);
    $ids_to_add_to_unread_table = array();
    $ids_to_remove_from_unread_table = array();
    $ids_in_unread_table = array();
    $uid = $user['UserId'];
    debug("Marking posts as read for user $uid:");
    debug($post_ids);
    if (!sql_query_into($result, "SELECT PostId FROM ".FORUMS_UNREAD_POST_TABLE." WHERE UserId=$uid;", 0)) {
        debug_die("Error marking posts as read!");
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $ids_in_unread_table[$row['PostId']] = $row['PostId'];
    }
    debug("Iterating from ".($user['SeenPostsUpToId'] + 1)." to $max_id");
    for ($id = $user['SeenPostsUpToId'] + 1; $id <= $max_id; $id++) {
        if (in_array($id, $ids_in_unread_table)) {
            debug_die("Id in unread post table out of bounds!");
            return;
        }
        $ids_to_add_to_unread_table[$id] = $id;
    }
    foreach ($post_ids as $id) {
        if ($id > $user['SeenPostsUpToId']) {
            // Don't add to table.
            unset($ids_to_add_to_unread_table[$id]);
        } else {
            // Remove from table.
            if (in_array($id, $ids_in_unread_table)) {
                $ids_to_remove_from_unread_table[$id] = $id;
            }
        }
    }
    if ($user['SeenPostsUpToId'] < $max_id) {
        $user['SeenPostsUpToId'] = $max_id;
        if (!sql_query("UPDATE ".FORUMS_USER_PREF_TABLE." SET SeenPostsUpToId=$max_id WHERE UserId=$uid;")) {
            debug_die("Error while updating max_read_id.");
            return;
        }
    }
    if (sizeof($ids_to_remove_from_unread_table) > 0) {
        $joined = implode(",", $ids_to_remove_from_unread_table);
        if (!sql_query("DELETE FROM ".FORUMS_UNREAD_POST_TABLE." WHERE UserId=$uid AND PostId IN ($joined);")) {
            debug_die("Error while deleting ids from unread posts table.");
            return;
        }
    }
    if (sizeof($ids_to_add_to_unread_table) > 0) {
        $joined = implode(",", array_map(function($id) use ($uid) { return "($uid, $id)"; }, $ids_to_add_to_unread_table));
        if (!sql_query("INSERT INTO ".FORUMS_UNREAD_POST_TABLE." (UserId, PostId) VALUES $joined;")) {
            debug_die("Error while deleting ids from unread posts table.");
            return;
        }
    }
}

?>