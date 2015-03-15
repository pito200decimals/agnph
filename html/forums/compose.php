<?php
// Page for composing a new forum post.
// URL: /forums/create/{board-id}/
// URL: /forums/reply/{post/thread-id}/
// URL: /forums/edit/{post-id}/
// URL: /forums/compose.php?action={create|reply|edit}&thread={thread-id}&post={post-id}&board={board-id}

// Site includes, including login authentication.
include_once("../header.php");
include_once(__DIR__."/includes/functions.php");

if (!isset($user)) {
    // User is not logged in.
    RenderErrorPage("Must be logged in to post.");
    return;
}
if (!isset($_GET['action'])) {
    // Missing the type of edit action.
    RenderErrorPage("Invalid URL.");
    return;
}
if (($_GET['action'] == "create" && isset($_GET['board']) && is_numeric($_GET['board']))
    || ($_GET['action'] == "reply" && isset($_GET['thread']) && is_numeric($_GET['thread']))
    || ($_GET['action'] == "edit" && isset($_GET['post']) && is_numeric($_GET['post']))) {
    // Good parameters.
} else {
    RenderErrorPage("Invalid URL.");
    return;
}
if ($_POST) {
    $form_values = array();
    // Try to submit user data. If invalid, fall back to the already-initialized compose form.
    if (isset($_POST['submit'])
        && isset($_POST['title'])
        && isset($_POST['content'])
        && $_POST['submit'] == "Post") {
        // Try to post to thread.
        
        // Explicit input.
        $title = $_POST['title'];
        $escaped_title = sql_escape($title);
        $content = SanitizeHTMLTags($_POST['content'], DEFAULT_ALLOWED_TAGS);
        $escaped_content = sql_escape($content);
        
        // Implicit input.
        $uid = $user['UserId'];
        $date = time();
        
        if ($_GET['action'] == "create") {
            // Creating a new thread.
            $board_id = $_GET['board'];
            $escaped_board_id = sql_escape($board_id);
            if (CanUserPostToBoard($user, $board_id)) {
                if (strlen($title) > 0) {
                    if (strlen($content) > 0) {
                        $escaped_ip_addr = sql_escape($_SERVER['REMOTE_ADDR']);
                        $sticky = isset($_POST['sticky']) && $_POST['sticky'] && CanUserStickyThread($user, $board_id);
                        if (sql_query_into($result,
                            "INSERT INTO ".FORUMS_POST_TABLE."
                            (ParentLobbyId, Title, PostDate, UserId, Content, Sticky, PostIP)
                            VALUES
                            ('$escaped_board_id', '$escaped_title', $date, $uid, '$escaped_content', '$sticky', '$escaped_ip_addr');", 0)) {
                            // Success!
                            $pid = sql_last_id();
                            // Try to mark this post as read. Don't handle any sql errors.
                            MarkPostsAsRead($user, array($pid));
                        } else {
                            $form_values['error_msg'] = "Error creating thread.";
                        }
                    } else {
                        $form_values['error_msg'] = "Can't create thread with empty message.";
                        $result = false;
                    }
                } else {
                    $form_values['error_msg'] = "Can't create thread with empty title.";
                    $result = false;
                }
            } else {
                $form_values['error_msg'] = "Not authorized to create a thread on this board.";
                $result = false;
            }
        } else if ($_GET['action'] == "reply") {
            // Replying to an existing thread.
            $thread_id = $_GET['thread'];
            $escaped_thread_id = sql_escape($thread_id);
            if (CanUserPostToThread($user, $thread_id)) {
                if (strlen($title) > 0) {
                    if (strlen($content) > 0) {
                        $result = sql_query("INSERT INTO ".FORUMS_POST_TABLE." (UserId, PostDate, ParentThreadId, Title, Content) VALUES ($uid, $date, '$escaped_thread_id', '$escaped_title', '$escaped_content');");
                        $pid = sql_last_id();
                        // Try to mark this post as read. Don't handle any sql errors.
                        MarkPostsAsRead($user, array($pid));
                    } else {
                        $form_values['error_msg'] = "Can't reply to thread with empty message.";
                        $result = false;
                    }
                } else {
                    $form_values['error_msg'] = "Can't reply to thread with empty title.";
                    $result = false;
                }
            } else {
                $form_values['error_msg'] = "Not authorized to reply to this thread.";
                $result = false;
            }
        } else if($_GET['action'] == "edit") {
            // Modifying an existing post.
            $post_id = $_GET['post'];
            $escaped_post_id = sql_escape($post_id);
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';", 1)) {
                $post = $result->fetch_assoc();
                if (CanUserEditPost($user, $post)) {
                    if (strlen($title) > 0) {
                        if (strlen($content) > 0) {
                            $sticky = $post['ParentThreadId'] == -1 && isset($_POST['sticky']) && $_POST['sticky'] && CanUserStickyThread($user, $post['ParentLobbyId']);
                            $result = sql_query("UPDATE ".FORUMS_POST_TABLE." SET EditDate=$date, Title='$escaped_title', Content='$escaped_content', Sticky='$sticky' WHERE PostId='$escaped_post_id';");
                            $pid = $post['PostId'];
                        } else {
                            $form_values['error_msg'] = "Can't edit post with empty message.";
                            $result = false;
                        }
                    } else {
                        $form_values['error_msg'] = "Can't edit post with empty title.";
                        $result = false;
                    }
                } else {
                    $form_values['error_msg'] = "Not authorized to edit this post.";
                    $result = false;
                }
            } else {
                // Can't find post to edit.
                $form_values['error_msg'] = "Can't find post to edit.";
                $result = false;
            }
        }
        if ($result) {
            // On successful reply/create, return to end of thread. For edit, return to page that contains that post.
            // Upon error here, just revert to /forums/.
            function err_func($msg) {
                debug_die($msg, __FILE__, __LINE__);
                header("Location: /forums/");
                exit();
            }
            if ($_GET['action'] == "create") {
                // Jump to end of thread (which is also the begining).
                $tid = $pid;
                header("Location: /forums/thread/$tid/");
                return;
            } else if ($_GET['action'] == "reply") {
                // Jump to end of thread.
                sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$pid;", 1) or err_func("Failed to get edited post $pid.");
                $post = $result->fetch_assoc();
                $tid = $post['ParentThreadId'];
                if ($tid == -1) $tid = $pid;
                sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$tid OR ParentThreadId=$tid ORDER BY PostId;", 1) or err_func("Failed to get posts in thread $tid.");
                $offset = $result->num_rows - 1;
                header("Location: /forums/thread/$tid/$offset/");
                return;
            } else if ($_GET['action'] == "edit") {
                // Return to page of the edited post.
                sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$pid;", 1) or err_func("Failed to get edited post $pid.");
                $post = $result->fetch_assoc();
                $tid = $post['ParentThreadId'];
                if ($tid == -1) $tid = $pid;
                sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId=$tid OR ParentThreadId=$tid ORDER BY PostId;", 1) or err_func("Failed to get posts in thread $tid.");
                $offset = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($row['PostId'] == $pid) break;
                    $offset++;
                }
                header("Location: /forums/thread/$tid/$offset/");
                return;
            } else {
                err_func("Invalid GET action: ".$_GET['action']);
            }
        }
    }
    // Initialize with old data.
    $form_values['title'] = $_POST['title'];
    $form_values['content'] = $_POST['content'];
}
// Will be unset later if the user doesn't have perms.
if (isset($_POST) && isset($_POST['sticky'])) {
    $form_values['sticky'] = true;
} else {
    $form_values['sticky'] = false;
}

//////////////////////////////////////////////////////
// Post didn't exist or failed, load page normally. //
//////////////////////////////////////////////////////

if ($_GET['action'] == "create") {
    // Create the form for composing a new message here.
    $vars['formTitle'] = "Create Thread:";
    if (!isset($form_values['title'])) {
        $form_values['title'] = "";
    }
    $board_id = $_GET['board'];
    if (!CanUserStickyThread($user, $board_id)) {
        unset($form_values['sticky']);
    }
    CreateEditorForm($form_values) or RenderErrorPage("Unable to load compose page.");
} else if ($_GET['action'] == "reply") {
    // Create the form for composing a reply to an existing post here.
    $thread_id = $_GET['thread'];
    $escaped_thread_id = sql_escape($thread_id);
    sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_thread_id';", 1) or RenderErrorPage("Post not found.");
    $thread = $result->fetch_assoc();
    $vars['formTitle'] = "Create Reply:";
    $vars['postsTitle'] = "Thread:";
    DisplayRecentPosts($thread_id) or RenderErrorPage("Unable to load compose page.");
    $posts =&$vars['posts'];
    SetPostLinks($posts, true);
    if (!isset($form_values['title'])) {
        $form_values['title'] = "RE: ".$thread['Title'];
    }
    unset($form_values['sticky']);  // Never can sticky replies.
    CreateEditorForm($form_values) or RenderErrorPage("Unable to load compose page.");
    GetBreadcrumbsFromPost($thread, $names, $links) or RenderErrorPage("Thread not found.");
    $vars['crumbs'] = CreateCrumbsHTML($names, $links);
} else if ($_GET['action'] == "edit") {
    // Create the form for editing a single forum post message.
    $vars['formTitle'] = "Edit Post:";
    $escaped_pid = sql_escape($_GET['post']);
    sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_pid';", 1) or RenderErrorPage("Post not found.");
    $post = $result->fetch_assoc();
    CanUserEditPost($user, $post) or RenderErrorPage("Not authorized to edit this post.");
    $form_values['content'] = $post['Content'];
    if (!isset($form_values['title'])) {
        $form_values['title'] = $post['Title'];
    }
    $tid = $post['ParentThreadId'];
    if ($tid != -1 || !CanUserStickyThread($user, $post['ParentLobbyId'])) {
        // No sticky if not first post, or user can't sticky.
        unset($form_values['sticky']);
    } else if ($post['Sticky']) {
        $form_values['sticky'] = true;
    } else {
        $form_values['sticky'] = false;
    }
    CreateEditorForm($form_values) or RenderErrorPage("Unable to load compose page.");
    GetBreadcrumbsFromPost($post, $names, $links) or RenderErrorPage("Thread not found.");
    $vars['crumbs'] = CreateCrumbsHTML($names, $links);
}

RenderPage("forums/compose.tpl");
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
function CreateEditorForm($form_values) {
    global $vars;
    if (!isset($form_values)) debug_die("Unset form_values", __FILE__, __LINE__);
    $request_uri = $_SERVER['REQUEST_URI'];
    $initial_text = GetWithDefault($form_values, 'content', "");
    $title = GetWithDefault($form_values, 'title', "");
    $error_msg = (isset($form_values['error_msg'])) ?
        "<p>".$form_values['error_msg']."</p>" : "";
    if (isset($form_values['sticky'])) {
        // Display checkbox.
        $checked = $form_values['sticky'] ? "checked" : "";
        $sticky_box = "<p><input name='sticky' value='true' type='checkbox' $checked /><label for='sticky'>Mark thread as sticky</label></p>";
    } else {
        $sticky_box = "";
    }
    $editorForm = <<<EOD
<form action="$request_uri" method="post">
    $error_msg
    <p>Title: <input type='text' name='title' value='$title'/></p>
    <p><textarea name="content">$initial_text</textarea></p>
    $sticky_box
    <p><input name="submit" value="Post" type="submit" /></p>
</form>
EOD;
    $vars['editorForm'] = $editorForm;
    return true;
}
?>