<?php
// PHP submit page for changing the status of a post.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserEditPost($user)) {
    RenderErrorPage("Not authroized to edit post");
}
if (!isset($_POST['post']) || !isset($_POST['action'])) InvalidURL();

$user_id = $user['UserId'];
$post_id = $_POST['post'];
$escaped_post_id = sql_escape($post_id);
// Check for post existance.
if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1)) {
    RenderErrorPage("Post not found");
}
$post = $result->fetch_assoc();

if ($_POST['action'] == "delete" && CanUserDeletePost($user) && $post['Status'] != 'D') {
    // Delete post and return. Also remove from any pools and favorites.
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='D', FlaggerUserId='$user_id', ParentPoolId=-1 WHERE PostId='$escaped_post_id';")) {
        RenderErrorPage("Error processing request");
    }
    // Remove from favorites. Don't check for errors since we can't do anything.
    sql_query("DELETE FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE PostId='$escaped_post_id';");
    header("Location: /gallery/post/show/$post_id/");
    return;
}
if ($_POST['action'] == "undelete" && CanUserDeletePost($user) && ($post['Status'] == 'F' || $post['Status'] == 'D')) {
    // Undelete post and return.
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='A' WHERE PostId='$escaped_post_id';")) {
        RenderErrorPage("Error processing request");
    }
    header("Location: /gallery/post/show/$post_id/");
    return;
}
if ($_POST['action'] == "flag" && CanUserEditPost($user) && ($post['Status'] != 'F' && $post['Status'] != 'D') && isset($_POST['reason'])) {
    // Flag post and return.
    $reason = $_POST['reason'];
    $reason = substr($reason, 0, MAX_GALLERY_POST_FLAG_REASON_LENGTH);
    $escaped_reason = sql_escape($reason);
    debug($reason);
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='F', FlagReason='$escaped_reason', FlaggerUserId='$user_id' WHERE PostId='$escaped_post_id';")) {
        RenderErrorPage("Error processing request");
    }
    header("Location: /gallery/post/show/$post_id/");
    return;
}
if ($_POST['action'] == "unflag" && CanUserDeletePost($user) && $post['Status'] == 'F') {
    // Unflag post.
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='A' WHERE PostId='$escaped_post_id';")) {
        RenderErrorPage("Error processing request");
    }
    header("Location: /gallery/post/show/$post_id/");
    return;
}
if ($_POST['action'] == "approve" && CanUserApprovePost($user) && $post['Status'] == 'P') {
    // Approve post and return.
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='A' WHERE PostId='$escaped_post_id';")) {
        RenderErrorPage("Error processing request");
    }
    header("Location: /gallery/post/show/$post_id/");
    return;
}
RenderErrorPage("Error processing request");
?>