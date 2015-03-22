<?php
// Page for viewing a single post.
// URL: /gallery/post/show/{post-id}/
// URL: /gallery/posts/viewpost.php?post={post-id}

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($_GET['post'])) {
    RenderErrorPage("Invalid URL.");
    return;
}

$pid = $_GET['post'];
$escaped_post_id = sql_escape($pid);
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or RenderErrorPage("Post not found.");
$post = $result->fetch_assoc();
$pid = $post['PostId'];  // Get safe value, not user-generated.
$vars['post'] = $post;

$md5 = $post['Md5'];
$ext = $post['Extension'];
if ($post['HasPreview']) {
    $vars['previewUrl'] = GetSitePreviewPath($md5, $ext);
} else {
    $vars['previewUrl'] = GetSiteImagePath($md5, $ext);
}
$vars['downloadUrl'] = GetSiteImagePath($md5, $ext);

$allTagIds = array();
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$pid;", 0) or RenderErrorPage("Post not found.");
while ($row = $result->fetch_assoc()) {
    $allTagIds[] = $row['TagId'];
}
$allTags = array();
if (sizeof($allTagIds) > 0) {
    sql_query_into($result, "SELECT * FROM ".GALLERY_TAG_TABLE." WHERE TagId IN (".implode(",", $allTagIds).");", 1) or RenderErrorPage("Post not found.");
    while ($row = $result->fetch_assoc()) {
        $allTags[] = $row;
    }
}

$tagCategories = array();
foreach ($GALLERY_TAG_TYPES as $char => $name) {
    $category = array();
    $category['name'] = $name;
    $category['tags'] = array();
    foreach ($allTags as $tag) {
        if ($tag['Type'] == $char) {
            $tag['displayName'] = TagNameToDisplayName($tag['Name']);
            $category['tags'][] = $tag;
        }
    }
    if (sizeof($category['tags']) > 0) {
        $tagCategories[] = $category;
    }
}
$vars['post']['tagCategories'] = $tagCategories;

RenderPage("gallery/posts/viewpost.tpl");
return;
?>