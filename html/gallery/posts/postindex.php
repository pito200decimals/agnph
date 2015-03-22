<?php
// Page for viewing the search index of posts.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

$posts = array();
if (isset($_GET['search'])) {
    debug("Searching for '".$_GET['search']."'");
    $sql = CreatePostSearchSQL($_GET['search']);
} else {
    $sql = CreatePostSearchSQL("");
}
sql_query_into($result, $sql, 0) or RenderErrorPage("No posts found");
while ($row = $result->fetch_assoc()) {
    $md5 = $row['Md5'];
    $ext = $row['Extension'];
    $row['thumbnail'] = GetSiteThumbPath($md5, $ext);
    $posts[] = $row;
}

$vars['posts'] = $posts;

RenderPage("gallery/posts/postindex.tpl");
return;
?>