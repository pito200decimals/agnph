<?php
// Page for viewing the tag history of a post.
// URL: /gallery/post/show/{post-id}/history/

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");

if (!isset($_GET['post']) || !is_numeric($_GET['post'])) {
    InvalidURL();
}
$pid = $_GET['post'];
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId=$pid;", 1) or RenderErrorPage("Post not found");
$post = $result->fetch_assoc();
$pid = $post['PostId'];  // Get database value, not user input.

CollectItems(GALLERY_POST_TAG_HISTORY_TABLE, "WHERE PostId=$pid ORDER BY Timestamp DESC", $tag_history_items, GALLERY_LIST_ITEMS_PER_PAGE, $iterator, function($i) use ($pid) {
    return "/gallery/post/show/$pid/history/?page=$i";
}, "Post not found");

if (sizeof($tag_history_items) > 0) {
include(SITE_ROOT."gallery/includes/tag_history_include.php");
}

$vars['tagHistoryItems'] = $tag_history_items;
$vars['postIterator'] = $iterator;

RenderPage("gallery/posts/tag_history_index.tpl");
return;
?>