<?php
// Common utility functions for managing comments.

include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/user.php");

function ConstructCommentBlockIterator(&$items, &$iterator, $is_offset, $url_fn, $max_comments) {
    if (sizeof($items) > $max_comments) {
        if ($is_offset && isset($_GET['offset']) && is_numeric($_GET['offset']) && ((int)$_GET['offset']) >= 0) $offset = $_GET['offset'];
        else $offset = 0;
        $iterator = Paginate($items, $offset, $max_comments,
            function($index, $current_page, $max_page) use ($url_fn) {
                if ($index == 0) {
                    if ($current_page == 1) {
                        return "";  // No link.
                    } else {
                        $url = $url_fn($current_page - 1);
                        return "<a href='$url'>&lt;&lt;</a>";
                    }
                } else if ($index == $max_page + 1) {
                    if ($current_page == $max_page) {
                        return "";  // No link.
                    } else {
                        $url = $url_fn($current_page + 1);
                        return "<a href='$url'>&gt;&gt;</a>";
                    }
                } else if ($index == $current_page) {
                    return "<a>[$index]</a>";  // No link.
                } else {
                        $url = $url_fn($index);
                    return "<a href='$url'>$index</a>";
                }
            }, true);
    }
}
?>