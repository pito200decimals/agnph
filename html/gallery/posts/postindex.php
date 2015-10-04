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
HandlePost($searchterms);
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

// Set up permissions.
if (isset($user)) {
    $vars['canMassTagEdit'] = CanUserMassTagEdit($user) && mb_strlen($searchterms) > 0;
}

$vars['posts'] = $posts;
$vars['cansort'] = $can_sort_pool;
if ($can_sort_pool) {
    $vars['poolId'] = $pool_id;
}

if (isset($_SESSION['disable-gallery-mobile'])) {
    $vars['ignore_mobile'] = $_SESSION['disable-gallery-mobile'];
} else {
    $vars['ignore_mobile'] = false;
}

RenderPage("gallery/posts/postindex.tpl");
return;

function CreatePageIterator($searchterms, $page, $posts_per_page) {
    $total_num_posts = CountNumPosts(mb_strtolower($searchterms));
    $num_max_pages = (int)(($total_num_posts + $posts_per_page - 1) / $posts_per_page);
    if ($num_max_pages > 1) {
        $iterator_html = ConstructPageIterator($page, $num_max_pages, DEFAULT_PAGE_ITERATOR_SIZE,
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

function HandlePost($searchterms) {
    // TODO: Handle timeouts.
    global $user;
    if (isset($user) && isset($_POST['submit'])) {
        if (!CanPerformSitePost()) MaintenanceError();
        if (!CanUserMassTagEdit($user)) {
            RenderPostError("Insufficient permissions");
        }
        $where_clause = CreatePostSearchSQL(mb_strtolower($searchterms), 0, 0, $can_sort_pool, $pool_id, true);
        sql_query_into($result, "SELECT COUNT(*) AS C FROM ".GALLERY_POST_TABLE." T WHERE $where_clause;", 1) or RenderPostError("Error modifying posts");
        $num_posts = $result->fetch_assoc()['C'];
        if ($num_posts > GALLERY_MAX_MASS_TAG_EDIT_COUNT) {
            RenderPostError("Cannot modify $num_posts posts (max limit ".GALLERY_MAX_MASS_TAG_EDIT_COUNT.")");
        }
        if ($num_posts == 0) {
            RenderPostError("No posts to modify");
        }

        // Get next batch id.
        sql_query_into($result, "SELECT MAX(BatchId) AS M FROM ".GALLERY_POST_TAG_HISTORY_TABLE.";", 1) or RenderPostError("Error modifying posts");
        $next_batch_id = $result->fetch_assoc()['M'] + 1;

        // Get posts to modify.
        $post_ids = array();
        sql_query_into($result, "SELECT PostId FROM ".GALLERY_POST_TABLE." T WHERE $where_clause;", 1) or RenderPostError("Error modifying posts");
        while ($row = $result->fetch_assoc()) {
            $post_ids[] = $row['PostId'];
        }

        // Get tags to add/remove.
        $tags_to_add = $_POST['tags-to-add'];
        $tags_to_add = explode(" ", $tags_to_add);
        $tags_to_add = array_map("trim", $tags_to_add);
        $tags_to_add = array_filter($tags_to_add, "mb_strlen");
        $tags_to_remove = $_POST['tags-to-remove'];
        $tags_to_remove = explode(" ", $tags_to_remove);
        $tags_to_remove = array_map("trim", $tags_to_remove);
        $tags_to_remove = array_filter($tags_to_remove, "mb_strlen");
        $can_create_tags = CanUserCreateGalleryTags($user);
        // For add tags, do alias, implication, and remove aliased tags.
        $tags_to_add = GetTagsByNameWithAliasAndImplied(GALLERY_TAG_TABLE, GALLERY_TAG_ALIAS_TABLE, GALLERY_TAG_IMPLICATION_TABLE, $tags_to_add, $can_create_tags, $user['UserId']);
        // For remove tags, do alias, no implications, and keep aliased tags. Also, don't create tags being removed.
        $tags_to_remove = GetTagsByNameWithAliasAndImplied(GALLERY_TAG_TABLE, GALLERY_TAG_ALIAS_TABLE, GALLERY_TAG_IMPLICATION_TABLE, $tags_to_remove, false, $user['UserId'], true, false, false);
        // Convert to names, but don't add any tags we're also removing.
        $tag_ids_to_remove = array_map(function($tag) { return $tag['TagId']; }, $tags_to_remove);
        $tags_to_add = array_filter($tags_to_add, function($tag) use ($tag_ids_to_remove) {
            return !in_array($tag['TagId'], $tag_ids_to_remove);
        });
        $tag_ids_to_add = array_map(function($tag) { return $tag['TagId']; }, $tags_to_add);

        foreach ($post_ids as $pid) {
            $existing_tags = GetTags($pid);
            $existing_tags = array_filter($existing_tags, function($tag) use ($tag_ids_to_remove,$tag_ids_to_add) {
                return !in_array($tag['TagId'], $tag_ids_to_remove) && !in_array($tag['TagId'], $tag_ids_to_add);
            });
            $existing_tags = array_merge($existing_tags, $tags_to_add);
            $tag_str = ToTagNameString($existing_tags);
            UpdatePost($tag_str, $pid, $user, false, $next_batch_id);
        }

        PostSessionBanner("Posts modified", "green");
        Redirect($_SERVER['REQUEST_URI']);
    }
}

function RenderPostError($msg) {
    PostSessionBanner($msg, "red");
    Redirect($_SERVER['REQUEST_URI']);
}

?>