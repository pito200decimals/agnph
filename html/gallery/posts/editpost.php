<?php
// Page for receiving POSTS to edit a gallery post.
// URL: /gallery/post/edit/

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserEditPost($user)) {
    RenderErrorPage("Not authorized to edit posts.");
    return;
}

if (!isset($_POST) ||
    !isset($_POST['post']) ||
    !is_numeric($_POST['post']) ||
    !isset($_POST['rating']) ||
    strlen($_POST['rating']) != 1 ||
    !isset($_POST['parent']) ||
    !(strlen($_POST['parent']) == 0 || is_numeric($_POST['parent'])) ||
    !isset($_POST['source']) ||
    !isset($_POST['tags']) ||
    !isset($_POST['description'])) {
    RenderErrorPage("Invalid URL.");
    return;
}

$post_id = $_POST['post'];
$escaped_post_id = sql_escape($post_id);
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or RenderErrorPage("Post not found.");
$post = $result->fetch_assoc();

$tagstr = preg_replace("/\s+/", " ", $_POST['tags']);
$tagstrarray = explode(" ", $tagstr);
$tagstrarray = array_filter($tagstrarray, function($str) { return strlen($str) > 0; });
$tagstrarray[] = "rating:".$_POST['rating'];
$parent_post_id = GetValidParentPostId($_POST['parent'], $post_id);
$tagstrarray[] = "parent:$parent_post_id";
$tagstrarray[] = "source:".substr(str_replace(" ", "%20", $_POST['source']), 0, 256);
$tagstr = implode(" ", $tagstrarray);

DoAllProcessTagString($tagstr, $post_id, $user['UserId']);

header("Location: /gallery/post/show/$post_id/");
?>