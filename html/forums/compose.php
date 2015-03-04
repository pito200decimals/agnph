<?php
// Page for composing a new forum post.
// URL: /forums/create/
// URL: /forums/reply/{thread-id}/
// URL: /forums/edit/{post-id}/
// URL: /forums/compose.php?action={create|reply|edit}&thread={thread-id}&post={post-id}

// Site includes, including login authentication.
include_once("../header.php");
include_once(__DIR__."/includes/functions.php");

if (!isset($user)) {
    // User is not logged in.
    $vars['error_msg'] = "Must be logged in to post.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if (!isset($_GET['action'])) {
    // Missing the type of edit action.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if (($_GET['action'] == "create" && isset($_GET['board']) && is_numeric($_GET['board']))
    || ($_GET['action'] == "reply" && isset($_GET['thread']) && is_numeric($_GET['thread']))
    || ($_GET['action'] == "edit" && isset($_GET['post']) && is_numeric($_GET['post']))) {
    // Good parameters.
} else {
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}

if ($_POST) {
    $form_values = array();
    // Try to submit user data. If invalid, fall back to the already-initialized compose form.
    if ($_POST['submit'] == "Post") {
        // Try to post to thread.
        $content = $_POST['content'];
        $content = SanitizeHTMLTags($content, DEFAULT_ALLOWED_TAGS);
        $escaped_content = sql_escape($content);
        $uid = $user['UserId'];
        $date = time();
        if ($_GET['action'] == "create") {
            // Creating a new thread.
            $bid = $_GET['board'];
            if (isset($_POST['title']) && strlen($_POST['title']) > 0) {
                $escaped_title = sql_escape($_POST['title']);
                $result = sql_query("INSERT INTO ".FORUMS_THREAD_TABLE." (ParentLobbyId, Title, CreateDate, CreatorUserId) VALUES ($bid, '$escaped_title', $date, $uid);");
                if ($result) {
                    $tid = sql_last_id();
                    $result = sql_query("INSERT INTO ".FORUMS_POST_TABLE." (UserId, PostDate, ParentThreadId, Content) VALUES ($uid, $date, $tid, '$escaped_content');");
                    if ($result) {
                        $pid = sql_last_id();
                    } else {
                        // Error creating post, try and delete thread.
                        $result = sql_query("DELETE FROM ".FORUMS_THREAD_TABLE." WHERE ThreadId=$tid;");
                        if (!$result) {
                            // ERROR.
                            debug_die("ERROR, failed creating post under thread $tid, and failed deleting thread $tid.");
                        }
                    }
                }
            } else {
                $form_values['error_msg'] = "Cannot have empty thread title.";
                $result = false;
            }
        } else if ($_GET['action'] == "reply") {
            // Replying to an existing thread.
            $tid = sql_escape($_GET['thread']);
            $result = sql_query("INSERT INTO ".FORUMS_POST_TABLE." (UserId, PostDate, ParentThreadId, Content) VALUES ($uid, $date, $tid, '$escaped_content');");
            $pid = sql_last_id();
        } else if($_GET['action'] == "edit") {
            // Modifying an existing post.
            $pid = sql_escape($_GET['post']);
            $result = sql_query("UPDATE ".FORUMS_POST_TABLE." SET EditDate=$date,Content='$escaped_content' WHERE PostId=$pid;");
        }
        if ($result) {
            // On success, return to end of thread. Or at least try to. Upon error here, just revert to /forums/.
            $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$pid");
            if ($result && $result->num_rows > 0) {
                $post = $result->fetch_assoc();
                $tid = $post['ParentThreadId'];
                $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId=$tid ORDER BY PostId");
                if ($result && $result->num_rows > 0) {
                    $offset = $result->num_rows - 1;
                    $post = $result->fetch_assoc();
                    $tid = $post['ParentThreadId'];
                    header("Location: /forums/thread/$tid/$offset/");
                    return;
                } else {
                    debug_die("Died on second query");
                    header("Location: /forums/");
                    return;
                }
            } else {
                debug_die("Died on first query");
                header("Location: /forums/");
                return;
            }
        }
    }
    // Initialize with old data.
    $form_values['content'] = $_POST['content'];
}

// Post didn't exist or failed, load page normally.

if ($_GET['action'] == "create") {
    // Create the form for composing a new message here.
    $vars['title'] = "Create Thread:";
    if (CreateEditorForm(true, $form_values)) {
        // Done building page!
    } else {
        $vars['content'] = "Unable to load compose page.";
    }
} else if ($_GET['action'] == "reply") {
    // Create the form for composing a reply to an existing post here.
    $thread_id = $_GET['thread'];
    $vars['title'] = "Create Reply:";
    $vars['postsTitle'] = "Thread:";
    if (DisplayRecentPosts($thread_id)) {
        $posts =&$vars['posts'];
        SetPostLinks($posts, true);
        if (!isset($form_values)) {
            if (isset($quote_id) && isset($posts[$quote_id])) {
                $form_values['content'] = $posts[$quote_id]['Content'];
            } else {
                $form_values['content'] = "";
            }
        }
        if (CreateEditorForm(false, $form_values)) {
            // Done building page!
        } else {
            $vars['content'] = "Unable to load compose page.";
        }
    } else {
        $vars['content'] = "Unable to load compose page.";
    }
} else if ($_GET['action'] == "edit") {
    // Create the form for editing a single forum post message.
    $vars['title'] = "Edit Post:";
    $escaped_pid = sql_escape($_GET['post']);
    $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_pid'");
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
        $uid = $post['UserId'];
        // TODO: Also allow admins?
        if ($uid == $user['UserId']) {
            $form_values['content'] = $post['Content'];
            if (CreateEditorForm(false, $form_values)) {
                // Done building page!
            } else {
                $vars['content'] = "Unable to load compose page.";
            }
        } else {
            $vars['content'] = "Not authorized to edit this post.";
        }
    }
}

echo $twig->render("forums/compose.tpl", $vars);
return;

// Outputs to $vars['posts'] the most recent posts in the thread of the given id, in reverse-chronological order.
// Returns true on success, false on failure.
function DisplayRecentPosts($thread_id) {
    global $vars, $user;
    $posts = GetAllPostsInThread($thread_id);
    $posts = array_reverse($posts);
    if (isset($user)) {
        $posts_per_page = $user['PostsPerPage'];
    } else {
        $posts_per_page = DEFAULT_POSTS_PER_PAGE;
    }
    $posts = array_slice($posts, 0, $posts_per_page);
    if ($posts) {
        if(GetAllPosterData($posts)) {
            $vars['posts'] = $posts;
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Outputs to $vars['editorForm'], returns true on success, false on failure.
function CreateEditorForm($include_title = false, $form_values = array('content' => "")) {
    global $vars;
    $request_uri = $_SERVER['REQUEST_URI'];
    $initial_text = $form_values['content'];
    $title_box = $include_title ? "Thread Title: <input type='text' name='title' value=''/><br />" : "";
    if (isset($form_values['error_msg'])) {
        $error_msg = $form_values['error_msg']."<br />";
    } else {
        $error_msg = "";
    }
    $editorForm = <<<EOD
<form action="$request_uri" method="post">
    $error_msg
    $title_box
    <textarea name="content">$initial_text</textarea><br />
    <input name="submit" value="Post" type="submit" />
</form>
EOD;
    $vars['editorForm'] = $editorForm;
    return true;
}
?>