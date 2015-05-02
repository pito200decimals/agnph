<?php
// Page for receiving POSTS to edit a gallery post.
// URL: /gallery/post/edit/

define("DEBUG", true);

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
    mb_strlen($_POST['rating']) != 1 ||
    !isset($_POST['parent']) ||
    !(mb_strlen($_POST['parent']) == 0 || is_numeric($_POST['parent'])) ||
    !isset($_POST['source']) ||
    !isset($_POST['tags']) ||
    !isset($_POST['description'])) {
    InvalidURL();  // Missing gallery editpost $_POST parameters.
}

$post_id = $_POST['post'];
$escaped_post_id = sql_escape($post_id);
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or RenderErrorPage("Post not found.");
$post = $result->fetch_assoc();
$parent_post_id = GetValidParentPostId($_POST['parent'], $post_id);

// Append rating and stuff before tags, so that tags can override them.
$tagstr = "rating:".$_POST['rating']." parent:$parent_post_id source:".mb_substr(str_replace(" ", "%20", $_POST['source']), 0, 256)." ".$_POST['tags'];
$tagstr = mb_ereg_replace("\s+", " ", $tagstr);
$tagstrarray = explode(" ", $tagstr);
$tagstrarray = array_filter($tagstrarray, function($str) { return mb_strlen($str) > 0; });
$tagstr = implode(" ", $tagstrarray);

UpdatePost($tagstr, $post_id, $user);
// TODO: Update post description.

header("Location: /gallery/post/show/$post_id/");
?>