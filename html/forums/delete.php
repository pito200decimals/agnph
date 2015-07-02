<?php
// Page for deleting a post/thread.
// URL: /forums/delete/{post/thread-id}/
// URL: /forums/delete.php?post={post/thread-id}

// Site includes, including login authentication.
include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."forums/includes/functions.php");

if (!isset($user)) {
    // User is not logged in.
    $vars['error_msg'] = "Must be logged in edit posts.";
    RenderPage("base.tpl");
    return;
}
if (isset($_POST)
    && isset($_POST['post'])
    && is_numeric($_POST['post'])
    && isset($_POST['hash'])) {
    $hash = $_POST['hash'];
    $secret = md5($user['UserId'].$user['Password']);
    if ($hash != $secret) RenderErrorPage("Not authroized to delete post");
} else {
    InvalidURL();  // Missing delete forum post $_POST parameters.
}

$post_id = $_POST['post'];
$escaped_post_id = sql_escape($post_id);
sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or RenderErrorPage("Unable to find post.");
$post = $result->fetch_assoc();
CanUserDeleteForumsPost($user, $post) or RenderErrorPage("Not authorized to modify post.");
if ($post['ParentThreadId'] == -1) {
    // Is a root post of a thread. Only delete if there are no posts in that thread.
    sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE ParentThreadId='$escaped_post_id';", 0) or RenderErrorPage("Unable to find post.");
    $num_child_posts = $result->num_rows;
    ($num_child_posts == 0) or RenderErrorPage("Can't delete a thread with other posts in it. Ask an admin to delete the other posts first.");
    // Can delete.
    // Delete from unread items first, that way if it fails, we won't get orphaned unread posts.
    sql_query("DELETE FROM ".FORUMS_UNREAD_POST_TABLE." WHERE PostId='$escaped_post_id';") or RenderErrorPage("Unable to delete post.");
    sql_query("DELETE FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';") or RenderErrorPage("Unable to delete post.");
    // Success!
    // Return to board lobby.
    $board_id = $post['ParentLobbyId'];
    header("Location: /forums/board/$board_id/");
    return;
} else {
    // Is a post in a thread. Delete it.
    sql_query("DELETE FROM ".FORUMS_POST_TABLE." WHERE PostId='$escaped_post_id';") or RenderErrorPage("Unable to delete post.");
    // Success!
    // Return to thread at offset 0.
    $thread_id = $post['ParentThreadId'];
    header("Location: /forums/thread/$thread_id/");
    return;
}
?>