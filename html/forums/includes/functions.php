<?php
// General library of functions used in the forums section.

// Gets all posts in the given thread id. Returns the array of posts.
function GetAllPostsInThread($tid) {
    $escaped_tid = sql_escape($tid);
    $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId='$escaped_tid' ORDER BY PostId;");
    if (!$result) return null;
    $posts = array();
    while ($row = $result->fetch_assoc()) {
        $posts[$row['PostId']] = $row;
    }
    return $posts;
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
    $result = sql_query("SELECT * FROM ".FORUMS_THREAD_TABLE." WHERE ParentLobbyId=$board ORDER BY Sticky DESC, ThreadId DESC;");
    if ($result) {
        $threads = array();
        while ($row = $result->fetch_assoc()) {
            $thread = $row;
            $tid = $thread['ThreadId'];
            $thread['CreateDate'] = FormatDate($thread['CreateDate']);
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
        $user_ids[] = $thread['CreatorUserId'];
    }
    $users = array();
    if (LoadUsers($user_ids, $users)) {
        foreach ($threads as &$thread) {
            $thread['creator'] = $users[$thread['CreatorUserId']];
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
    if ($user) {
        // All user actions.
        $pid = $post['PostId'];
        $tid = $post['ParentThreadId'];
        if ($compose) {
            // Run scripts to load the text into the compose box.
            $post['quoteLink'] = "#quote";
        } else {
            $post['quoteLink'] = "/forums/reply/$tid/?quote=$pid";
        }
        // TODO: Add more actions on forum posts.
        if ($user['UserId'] == $post['UserId']) {
            // Owner user actions.
            //$post['modifyLink'] = "";
            //$post['deleteLink'] = "";
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