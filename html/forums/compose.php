<?php
// Page for composing a new forum post.
// URL: /forums/create/
// URL: /forums/reply/{post-id}/

// Site includes, including login authentication.
include_once("../header.php");
include_once(__DIR__."/includes/functions.php");

if (!isset($user)) {
    // User is not logged in.
    $vars['error_msg'] = "Must be logged in to post.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if (!isset($_GET['create'])) {
    // Missing create boolean var.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if ($_GET['create'] != "true" && $_GET['create'] != "false") {
    // Invalid boolean parameter.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if ($_GET['create'] == "false" && !isset($_GET['thread'])) {
    // Missing thread id when we want to reply to a thread.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if ($_GET['create'] == "false" && isset($_GET['quote']) && is_int($_GET['quote'])) {
    $quote_id = (int)$_GET['quote'];
}

if ($_POST) {
    // Try to submit user data. If invalid, fall back to the already-initialized compose form.
    if ($_POST['submit'] == "Post") {
        // Try to post to thread.
        // return;
    } else if ($_POST['submit'] == "Preview") {
        // Initialize with old data. Also initialize the new post preview block.
        $post_preview = array(
            'Content' => $_POST['content'],
            'poster' => $user,
        );
        $post_preview['Content'] = SanitizeHTMLTags($post_preview['Content'], DEFAULT_ALLOWED_TAGS);
        $vars['previewBlock'] = $post_preview;
    } else {
        // Initialize with old data.
    }
    unset($quote_id);
}

if ($_GET['create'] == "true") {
    // Create the form for composing a new message here.
    $vars['title'] = "Create Thread:";
    if (CreateEditorForm()) {
        // Done building page!
    } else {
        $vars['content'] = "Unable to load compose page.";
    }
} else {
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
        if (CreateEditorForm($form_values)) {
            // Done building page!
        } else {
            unset($vars['replyPost']);
            $vars['content'] = "Unable to load compose page.";
        }
    } else {
        unset($vars['replyPost']);
        $vars['content'] = "Unable to load compose page.";
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
    $escaped_reply_post_id = sql_escape($thread_id);
    $result = sql_query("SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentPostId='$escaped_reply_post_id' LIMIT 1;");
    if ($result && $result->num_rows > 0) {    
        $replyPost = $result->fetch_assoc();
        $posts = array($replyPost);
        if(GetAllPosterData($posts)) {
            $replyPost = $posts[0];
            $vars['replyPost'] = $replyPost;
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Outputs to $vars['editorForm'], returns true on success, false on failure.
function CreateEditorForm($form_values = array('content' => "")) {
    global $vars;
    $request_uri = $_SERVER['REQUEST_URI'];
    $initial_text = $form_values['content'];
    $editorForm = "Compose page, Initializing with '$initial_text'";
    $editorForm = <<<EOD
<form action="$request_uri" method="post">
    <textarea name="content">$initial_text</textarea><br />
    <input name="submit" value="Post" type="submit" />
    <input name="submit" value="Preview" type="submit" />
</form>
EOD;
    $vars['editorForm'] = $editorForm;
    return true;
}
?>