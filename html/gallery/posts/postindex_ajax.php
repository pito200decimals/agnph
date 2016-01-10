<?php
// Ajax page for fetching image paths for slideshow view.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (isset($_GET['page']) && is_numeric($_GET['page']) && ((int)$_GET['page']) > 0) {
    $page = $_GET['page'];
} else {
    $page = 1;
}
if (isset($_GET['search'])) {
    $searchterms = mb_ereg_replace('\s+', ' ', trim($_GET['search']));
    $vars['_title'] = "$searchterms - AGNPH - Gallery";
} else {
    $searchterms = "";
}
if (isset($user)) {
    $posts_per_page = $user['GalleryPostsPerPage'];
} else {
    $posts_per_page = DEFAULT_GALLERY_POSTS_PER_PAGE;
}
if (isset($_GET['pagesize']) && is_numeric($_GET['pagesize'])) {
    $pagesize = (int)$_GET['pagesize'];
    if (0 < $pagesize && $pagesize <= MAX_GALLERY_POSTS_PER_PAGE) {
        $posts_per_page = $pagesize;
    }
}

$sql = CreatePostSearchSQL(mb_strtolower($searchterms, "UTF-8"), $posts_per_page, $page, $can_sort_pool, $pool_id);
$total_base_offset = ($page - 1) * $posts_per_page;
$posts = array();
if ($can_sort_pool && $page > 1) {
    // There aren't any posts beyond the first page for pools.
} else {
    if (sql_query_into($result, $sql, 0)) {
        $page_offset = 1;
        while ($row = $result->fetch_assoc()) {
            // Only allow non-animated posts.
            $md5 = $row['Md5'];
            $ext = $row['Extension'];
            $width = $row['Width'];
            $height = $row['Height'];
            $pid = $row['PostId'];
            $src = GetSiteImagePath($md5, $ext);
            $offset = $total_base_offset + $page_offset;
            $post = array(
                "src" => $src,
                "w" => $width,
                "h" => $height,
                "postURL" => "/gallery/post/show/$pid/",
                "pid" => "p$offset"
            );
            $page_offset++;
            if ($ext == "jpg" || $ext == "png" || $ext == "gif") {
                // Leave as is.
            } else if ($ext == "swf") {
                continue;
            } else if ($ext == "webm") {
                continue;
            } else {
                continue;
            }
            $posts[] = $post;
        }
    }
}

echo json_encode($posts);
return;
?>