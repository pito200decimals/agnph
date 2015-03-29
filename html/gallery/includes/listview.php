<?php
// General setup for viewing a list of items.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

// Table name is the name of the table to select from. Can optionally append a WHERE, since $sql_order follows directly after.
// Order is the ordering of the items selected (include ORDER BY).
// Items stores the list of fetched items.
// Items per page is the number of items per page.
// Iterator stores the iterator HTML.
// Create function is of type $page => $link_url.
// Error message is display on error.
function CollectItems($table_name, $sql_order, &$items, $items_per_page, &$iterator, $create_iterator_link_fn, $error_msg) {
    if (isset($_GET['page']) && is_numeric($_GET['page']) && ((int)$_GET['page']) > 0) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    $offset = $items_per_page * ($page - 1);
    sql_query_into($result, "SELECT * FROM $table_name $sql_order LIMIT $items_per_page OFFSET $offset;", 0) or RenderErrorPage($error_msg);
    $items = array();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    // Get total number, and construct iterator.
    sql_query_into($result, "SELECT count(*) FROM $table_name;", 1) or RenderErrorPage($error_msg);
    $total_num_items = $result->fetch_assoc()['count(*)'];
    $num_max_pages = (int)(($total_num_items + $items_per_page - 1) / $items_per_page);
    if ($num_max_pages > 1) {
        $iterator = ConstructPageIterator($page, $num_max_pages, DEFAULT_GALLERY_PAGE_ITERATOR_SIZE,
            function($i, $current_page) use ($num_max_pages, $create_iterator_link_fn) {
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
                $url = $create_iterator_link_fn($i);
                return "<a href='$url'>$txt</a>";
            }, true);
    } else {
        $iterator = "";
    }
}
?>