<?php
// Page for viewing the search index of posts.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/listview.php");

if (isset($_GET['page']) && is_numeric($_GET['page']) && ((int)$_GET['page']) > 0) {
    $page = $_GET['page'];
} else {
    $page = 1;
}
if (isset($_GET['search'])) {
    $searchterms = mb_ereg_replace('\s+', ' ', trim($_GET['search']));
} else {
    $searchterms = "";
}
if (isset($user)) {
    $posts_per_page = $user['GalleryPostsPerPage'];
} else {
    $posts_per_page = DEFAULT_GALLERY_POSTS_PER_PAGE;
}
$vars['search'] = $searchterms;
$sql = CreatePostSearchSQL(mb_strtolower($searchterms), $posts_per_page, $page, $can_sort_pool, $pool_id);
$posts = array();
if (sql_query_into($result, $sql, 0)) {
    while ($row = $result->fetch_assoc()) {
        $md5 = $row['Md5'];
        $ext = $row['Extension'];
        $row['thumbnail'] = GetSiteThumbPath($md5, $ext);
        CreatePostLabel($row);
        $posts[] = $row;
    }
    SetOutlineClasses($posts);
}

// Construct page iterator.
$vars['postIterator'] = CreatePageIterator($searchterms, $page, $posts_per_page);

$vars['posts'] = $posts;
$vars['cansort'] = $can_sort_pool;
if ($can_sort_pool) {
    $vars['poolId'] = $pool_id;
}

RenderPage("gallery/posts/postindex.tpl");
return;

function CreatePageIterator($searchterms, $page, $posts_per_page) {
    $total_num_posts = CountNumPosts(mb_strtolower($searchterms));
    $num_max_pages = (int)(($total_num_posts + $posts_per_page - 1) / $posts_per_page);
    if ($num_max_pages > 1) {
        $iterator_html = ConstructPageIterator($page, $num_max_pages, DEFAULT_GALLERY_PAGE_ITERATOR_SIZE,
            function($i, $current_page) use ($searchterms, $num_max_pages) {
                if ($i == 0) {
                    if ($current_page == 1) {
                        return "<span class='currentpage'>&lt;&lt;</span>";
                    } else {
                        $txt = "&lt;&lt;";
                        $i = $current_page - 1;
                    }
                } else if ($i == $num_max_pages + 1) {
                    if ($current_page == $num_max_pages) {
                        return "<span class='currentpage'>&gt;&gt;</span>";
                    } else {
                        $txt = "&gt;&gt;";
                        $i = $current_page + 1;
                    }
                } else if ($i == $current_page) {
                    return "<span class='currentpage'>$i</span>";
                } else {
                    $txt = $i;
                }
                if (mb_strlen($searchterms) > 0) {
                    if ($i != 1) {
                        $url = "/gallery/post/?search=".urlencode($searchterms)."&page=$i";
                    } else {
                        $url = "/gallery/post/?search=".urlencode($searchterms);
                    }
                } else {
                    if ($i != 1) {
                        $url = "/gallery/post/?page=$i";
                    } else {
                        $url = "/gallery/post/";
                    }
                }
                $url = DefaultCreateIteratorLinkFn($i);
                return "<a href='$url'>$txt</a>";
            }, true);
        return $iterator_html;
    } else {
        return "";
    }
}

?>