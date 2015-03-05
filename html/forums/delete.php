<?php
// Page for deleting a post/thread.
// URL: /forums/delete/{post/thread-id}/
// URL: /forums/delete.php?post={post/thread-id}

// Site includes, including login authentication.
include_once("../header.php");
include_once(__DIR__."/includes/functions.php");

if (!isset($user)) {
    // User is not logged in.
    $vars['error_msg'] = "Must be logged in edit posts.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if (isset($_GET['post'])
    && is_numeric($_GET['post'])
    && isset($_POST)) {
    // Good arguments.
} else {
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}

$post_id = $_GET['post'];
$escaped_post_id = sql_escape($post_id);
if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';", 1)) {
    $post = $result->fetch_assoc();
    if (CanUserDeletePost($user, $post)) {
        if ($post['ParentThreadId'] == -1) {
            // Is a root post of a thread. Only delete if there are no posts in that thread.
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId='$escaped_post_id';", 0)) {
                $num_child_posts = $result->num_rows;
                if ($num_child_posts == 0) {
                    // Can delete.
                    if (sql_query("DELETE FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';")) {
                        // Success!
                        // Return to board lobby.
                        $board_id = $post['ParentLobbyId'];
                        header("Location: /forums/board/$board_id/");
                        return;
                    } else { 
                        // Error while deleting post.
                        $vars['error_msg'] = "Unable to delete post.";
                        echo $twig->render("base.tpl", $vars);
                        return;
                    }
                } else {
                    // Thread has other posts in it. Can't delete it.
                    $vars['error_msg'] = "Can't delete a thread with other posts in it. Ask an admin to delete the other posts first.";
                    echo $twig->render("base.tpl", $vars);
                    return;
                }
            } else {
                // Couldn't look up child posts.
                $vars['error_msg'] = "Unable to find post.";
                echo $twig->render("base.tpl", $vars);
                return;
            }
        } else {
            // Is a post in a thread. Delete it.
            if (sql_query("DELETE FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';")) {
                // Success!
                // Return to thread at offset 0.
                $thread_id = $post['ParentThreadId'];
                header("Location: /forums/thread/$thread_id/");
                return;
            } else {    
                // Error while deleting post.
                $vars['error_msg'] = "Unable to delete post.";
                echo $twig->render("base.tpl", $vars);
                return;
            }
        }
    } else {
        // No user permissions.
        $vars['error_msg'] = "Not authorized to modify post.";
        echo $twig->render("base.tpl", $vars);
        return;
    }
} else {
    // Can't find the post id.
    $vars['error_msg'] = "Unable to find post.";
    echo $twig->render("base.tpl", $vars);
    return;
}
?>