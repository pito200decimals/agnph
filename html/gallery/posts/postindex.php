<?php
// Page for viewing the search index of posts.

include_once("../../header.php");

sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE.";", 0) or RenderErrorPage("No posts found");
$posts = array();
while ($row = $result->fetch_assoc()) {
    $md5 = $row['Md5'];
    $ext = $row['Extension'];
    $path = "";
    $path .= substr($md5, 0, 2)."/";
    $path .= substr($md5, 2, 2)."/";
    $path .= "$md5.$ext";
    $row['thumbnail'] = "/gallery/data/thumb/$path";
    $posts[] = $row;
}
$vars['posts'] = $posts;

RenderPage("gallery/posts/postindex.tpl");
return;
?>