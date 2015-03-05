<?php
// General library of functions used in the forums section.

// Permissions functions
function CanUserEditPost($user, $post) {
    return isset($user) && isset($post) &&
        ($user['UserId'] == $post['UserId']);
}
function CanUserDeletePost($user, $post) {
    return CanUserEditPost($user, $post);
}
function CanUserPostToBoard($user, $board_id) {
    return true;
}
function CanUserPostToThread($user, $thread_post) {
    return true;
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
    if (LoadUsers($uids, $users, array(FORUMS_USER_PREF_TABLE))) {
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
    if (LoadUsers($user_ids, $users)) {
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
    if ($user) {
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
        if ($user['UserId'] == $post['UserId']) {
            // Owner user actions.
            $post['modifyLink'] = "/forums/edit/$pid/";
            $post['deleteLink'] = "/forums/delete/$pid/";
        }
        if (strpos($user['Permissions'], "F")) {
            // Admin user actions.
            //$post['modifyLink'] = "";
            //$post['deleteLink'] = "";
            //$post['moveLink'] = "";
        }
    }
}

?>