<?php
// Page for viewing the search index of posts.

define("PRETTY_PAGE_NAME", "Gallery");

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
    $vars['_title'] = "$searchterms - AGNPH - Gallery";
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
$vars['page'] = $page;
$offset = ($page - 1) * $posts_per_page;
$sql = CreatePostSearchSQL(mb_strtolower($searchterms, "UTF-8"), $posts_per_page, $page, $can_sort_pool, $pool_id);
$posts = array();
if (sql_query_into($result, $sql, 0)) {
    while ($row = $result->fetch_assoc()) {
        $md5 = $row['Md5'];
        $ext = $row['Extension'];
        $row['image_path'] = GetSiteImagePath($md5, $ext);
        $row['thumbnail'] = GetSiteThumbPath($md5, $ext);
        if ($row['HasPreview']) {
            $row['preview'] = GetSitePreviewPath($md5, $ext);
        }
        CreatePostLabel($row);
        $posts[] = $row;
    }
    SetOutlineClasses($posts);
}

// Add a featured post.
$has_feature = !isset($_GET['api']) && isset($_GET['feature']) && is_numeric($_GET['feature']) && !contains(mb_strtolower($searchterms), "pool:");
if ($has_feature) {
    $escaped_post_id = sql_escape($_GET['feature']);
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id' LIMIT 1;", 1)) {
        $row = $result->fetch_assoc();
        if ($row['Status'] != 'D') {
            $row['outlineClass'] = "featuredoutline";
            $md5 = $row['Md5'];
            $ext = $row['Extension'];
            $row['image_path'] = GetSiteImagePath($md5, $ext);
            $row['thumbnail'] = GetSiteThumbPath($md5, $ext);
            if ($row['HasPreview'] == "1") {
                $row['preview'] = GetSitePreviewPath($md5, $ext);
            }
            CreatePostLabel($row);
            array_unshift($posts, $row);
        }
    }
}

// Construct page iterator.
$total_num_items = 0;
$vars['postIterator'] = CreateGalleryIterator($searchterms, $page, $posts_per_page, $total_num_items);

// Assign additional vars.
$vars['total_num_items'] = $total_num_items;
$vars['offset'] = $offset;

// Get suggested tags.
$tag_tokens = GetTagStringTokens($searchterms);
$tag_tokens = StripTildeAndMinus($tag_tokens);
$similar_tags = GetSimilarTagsByName(GALLERY_TAG_TABLE, $tag_tokens, GALLERY_TAG_ALIAS_TABLE);
if (sizeof($similar_tags) > 0) {
    $vars['similar_tags'] = $similar_tags;
}

// Set up permissions.
if (isset($user)) {
    $vars['canMassTagEdit'] = CanUserMassTagEdit($user) && mb_strlen($searchterms) > 0;
    $vars['canMassDeletePosts'] = CanUserMassDeletePosts($user) && mb_strlen($searchterms) > 0;
    if ($vars['canMassDeletePosts']) {
        $vars['flag_reasons'] = GetPossibleFlagReasons();
    }
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

// Used for gallery slideshow.
$vars['pagesize'] = $posts_per_page;

// Return API results if specified.
if (isset($_GET['api'])) {
    $api_type = $_GET['api'];
    if ($api_type == "xml") {
        RenderPage("gallery/posts/postindex.xml.tpl");
        return;
    }
}

// For now, show a banner to highlight the launch of the new oekaki.
$promotion_banner_enabled = true;
if ($promotion_banner_enabled) {
    PostBanner("Check out the new Oekaki! <a href='http://agn.ph/oekaki/draw/'>Draw Now</a>", "green", true, true);
}

RenderPage("gallery/posts/postindex.tpl");
return;

function StripTildeAndMinus($terms) {
    $ret = array();
    foreach ($terms as $term) {
        while (startsWith($term, "~") || startsWith($term, "-")) {
            $term = mb_substr($term, 1);
        }
        $ret[] = $term;
    }
    return $ret;
}

function CreateGalleryIterator($searchterms, $page, $posts_per_page, &$total_num_posts) {
    $total_num_posts = CountNumPosts(mb_strtolower($searchterms, "UTF-8"));
    $maxpage = (int)(($total_num_posts + $posts_per_page - 1) / $posts_per_page);
    if ($maxpage > 1) {
        $url_fn = function($i) use ($posts_per_page, $searchterms) {
                $args = array();
                if (mb_strlen($searchterms) > 0) $args[] = "search=".urlencode($searchterms);
                if ($i != 1) $args[] = "page=$i";
                if (sizeof($args) > 0) $args = "?".implode("&", $args);
                else $args = "";
                return "/gallery/post/$args";
            };
        $iterator = ConstructDefaultPageIterator($page, $maxpage, DEFAULT_PAGE_ITERATOR_SIZE, $url_fn);
        $iterator_mobile = ConstructDefaultPageIterator($page, $maxpage, DEFAULT_MOBILE_PAGE_ITERATOR_SIZE, $url_fn);
        return "<span class='desktop-only'>$iterator</span><span class='mobile-only'>$iterator_mobile</span>";
    } else {
        return "";
    }
}

function HandlePost($searchterms) {
    // TODO: Handle timeouts.
    global $user;
    if (isset($user) && isset($_POST['submit']) && isset($_POST['mass-edit-action'])) {
        $action = $_POST['mass-edit-action'];
        if ($action == "tagedit") {
            if (!CanPerformSitePost()) MaintenanceError();
            if (!CanUserMassTagEdit($user)) {
                RenderPostError("Insufficient permissions");
            }
            $where_clause = CreatePostSearchSQL(mb_strtolower($searchterms, "UTF-8"), 0, 0, $can_sort_pool, $pool_id, true /* where_only */);
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
        } else if ($action == "delete") {
            if (!CanPerformSitePost()) MaintenanceError();
            if (!CanUserMassDeletePosts($user)) {
                RenderPostError("Insufficient permissions");
            }
            if (isset($_POST['tags-to-add']) && isset($_POST['tags-to-remove']) &&
                (mb_strlen($_POST['tags-to-add']) > 0 || mb_strlen($_POST['tags-to-remove']) > 0)) {
                // Accidental delete submit?
                RenderPostError("Did not delete posts (don't add tags to edit)");
            }
            $where_clause = CreatePostSearchSQL(mb_strtolower($searchterms, "UTF-8"), 0, 0, $can_sort_pool, $pool_id, true /* where_only */);
            sql_query_into($result, "SELECT COUNT(*) AS C FROM ".GALLERY_POST_TABLE." T WHERE $where_clause;", 1) or RenderPostError("Error modifying posts");
            $num_posts = $result->fetch_assoc()['C'];
            if ($num_posts > GALLERY_MAX_MASS_TAG_EDIT_COUNT) {
                RenderPostError("Cannot delete $num_posts posts (max limit ".GALLERY_MAX_MASS_TAG_EDIT_COUNT.")");
            }
            if ($num_posts == 0) {
                RenderPostError("No posts to delete");
            }

            if (!isset($_POST['reason-select'])) {
                RenderPostError("Missing deletion reason");
            }
            $reason = $_POST['reason-select'];
            if (contains($reason, "#")) {
                RenderPostError("Invalid deletion reason");
            }
            $reason = SanitizeHTMLTags($reason, NO_HTML_TAGS);  // Strip all tags.
            $reason = mb_substr($reason, 0, MAX_GALLERY_POST_FLAG_REASON_LENGTH);  // Trim to max length.
            $escaped_reason = sql_escape(GetSanitizedTextTruncated($reason, NO_HTML_TAGS, MAX_GALLERY_POST_FLAG_REASON_LENGTH));

            // Get posts to modify.
            $post_ids = array();
            sql_query_into($result, "SELECT PostId FROM ".GALLERY_POST_TABLE." T WHERE $where_clause;", 1) or RenderPostError("Error modifying posts");
            while ($row = $result->fetch_assoc()) {
                $post_ids[] = $row['PostId'];
            }

            $username = $user['DisplayName'];
            foreach ($post_ids as $pid) {
                if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='D', FlagReason='$escaped_reason', FlaggerUserId='$uid', ParentPoolId=-1, NumFavorites=0 WHERE PostId=$pid;")) {
                    ErrorBanner();
                    return;
                }
                LogAction("<strong><a href='/user/$uid/'>$username</a></strong> mass-deleted <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong>", "G");
                // Remove from user favorites. Don't check for errors since we can't do anything.
                // TODO: Move favorites to parent post?
                sql_query("DELETE FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE PostId=$pid;");
                UpdatePostStatistics($pid);
                // Update all tag counts for tags on this post.
                UpdateAllTagCounts(GALLERY_TAG_TABLE, GALLERY_POST_TAG_TABLE, GALLERY_POST_TABLE, "PostId", "I.Status<>'D'", "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." PT WHERE PT.TagId=T.TagId AND PT.PostId=$pid)");
            }

            PostSessionBanner("Posts deleted", "green");
            Redirect($_SERVER['REQUEST_URI']);
        } else if ($action == "undelete") {
            // TODO: Support mass un-deletions.
        } else {
            RenderPostError("Invalid mass edit action");
        }
    }
}

function RenderPostError($msg) {
    PostSessionBanner($msg, "red");
    Redirect($_SERVER['REQUEST_URI']);
}

?>