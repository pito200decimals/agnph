<?php
// Common utility functions for managing comments.

include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/user.php");

function ConstructCommentBlockIterator(&$items, &$iterator, $is_offset, $url_fn, $max_comments_per_page) {
    if (sizeof($items) > $max_comments_per_page) {
        if ($is_offset && isset($_GET['offset']) && is_numeric($_GET['offset']) && ((int)$_GET['offset']) >= 0) $offset = $_GET['offset'];
        else $offset = 0;

        Paginate($items, $offset, $max_comments_per_page, $curr_page, $maxpage);
        $iterator = ConstructDefaultPageIterator($curr_page, $maxpage, DEFAULT_PAGE_ITERATOR_SIZE, $url_fn);
        $iterator_mobile = ConstructDefaultPageIterator($curr_page, $maxpage, DEFAULT_MOBILE_PAGE_ITERATOR_SIZE, $url_fn);
        $iterator = "<span class='desktop-only'>$iterator</span><span class='mobile-only'>$iterator_mobile</span>";
    }
}
?>