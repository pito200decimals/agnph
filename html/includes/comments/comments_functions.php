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

        Paginate($items, $offset, $max_comments_per_page, $curr_page, $num_max_pages);
        $iterator = ConstructPageIterator($curr_page, $num_max_pages, DEFAULT_PAGE_ITERATOR_SIZE,
            function($i, $current_page) use ($num_max_pages, $url_fn) {
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
                $url = $url_fn($i);
                return "<a href='$url'>$txt</a>";
            }, true);
    }
}
?>