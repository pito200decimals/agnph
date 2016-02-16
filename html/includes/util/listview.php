<?php
// General setup for viewing a list of items.

include_once(SITE_ROOT."includes/util/html_funcs.php");

// Table name is the name of the table to select from. Can optionally append a WHERE, since $sql_order follows directly after.
// Order is the ordering of the items selected (include ORDER BY).
// Items stores the list of fetched items.
// Items per page is the number of items per page.
// Iterator stores the iterator HTML.
// Create function is of type $page => $link_url.
// Error message is display on error.
function CollectItems($table_name, $sql_order, &$items, $items_per_page, &$iterator, $error_msg = null) {
    CollectItemsComplex($table_name, "SELECT * FROM $table_name T", $sql_order, $sql_order, $items, $items_per_page, $iterator, $error_msg);
}
function CollectItemsComplex($table_name, $complicated_sql, $sql_order, $count_sql_order, &$items, $items_per_page, &$iterator, $error_msg = null) {
    global $vars;
    if (isset($_GET['page']) && is_numeric($_GET['page']) && ((int)$_GET['page']) > 0) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    $offset = $items_per_page * ($page - 1);
    if (!sql_query_into($result, "$complicated_sql $sql_order LIMIT $items_per_page OFFSET $offset;", 0)) {
        if ($error_msg != null) {
            RenderErrorPage($error_msg);
        }
        return;
    }
    $items = array();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    // Get total number, and construct iterator.
    sql_query_into($result, "SELECT count(*) as ListSize FROM $table_name T $count_sql_order;", 1) or RenderErrorPage($error_msg);
    $total_num_items = $result->fetch_assoc()['ListSize'];
    $maxpage = (int)(($total_num_items + $items_per_page - 1) / $items_per_page);
    if ($maxpage > 1) {
        $iterator = ConstructDefaultPageIterator($page, $maxpage, DEFAULT_PAGE_ITERATOR_SIZE, "DefaultCreateIteratorLinkFn");
        $iterator_mobile = ConstructDefaultPageIterator($page, $maxpage, DEFAULT_MOBILE_PAGE_ITERATOR_SIZE, "DefaultCreateIteratorLinkFn");
        $iterator = "<span class='desktop-only'>$iterator</span><span class='mobile-only'>$iterator_mobile</span>";
    } else {
        $iterator = "";
    }
    $vars['total_num_items'] = $total_num_items;
    $vars['offset'] = $offset;
}

function DefaultCreateIteratorLinkFn($i) {
    $url = $_SERVER['REQUEST_URI'];
    if (isset($_GET['page']) && contains($url, "page=".$_GET['page'])) {
        return str_replace("page=".$_GET['page'], "page=$i", $url);
    } else if (endsWith($url, "?")) {
        return $url."page=$i";
    } else if (contains($url, "?")) {
        return $url."&page=$i";
    } else {
        return $url."?page=$i";
    }
}

function GetURLForSortOrder($sort, $default_order = "asc", $reset_page_offset = true) {
    $order = null;
    if (isset($_GET['sort']) && strtolower($_GET['sort']) == strtolower($sort)) {
        // Same sort type, reverse direction.
        if (isset($_GET['order']) && strtolower($_GET['order']) == "desc") {
            $order = "asc";
        } else {
            $order = "desc";
        }
    } else {
        // Different sort type, use default descending order.
        $order = $default_order;
    }

    $url = $_SERVER['REQUEST_URI'];
    if (isset($_GET['sort']) && contains($url, "sort=".$_GET['sort'])) {
        $url = str_replace("sort=".$_GET['sort'], "sort=$sort", $url);
    } else if (endsWith($url, "?")) {
        $url .= "sort=$sort";
    } else if (contains($url, "?")) {
        $url .= "&sort=$sort";
    } else {
        $url .= "?sort=$sort";
    }
    if (isset($_GET['order']) && contains($url, "order=".$_GET['order'])) {
        $url = str_replace("order=".$_GET['order'], "order=$order", $url);
    } else if (endsWith($url, "?")) {
        $url .= "order=$order";
    } else if (contains($url, "?")) {
        $url .= "&order=$order";
    } else {
        $url .= "?order=$order";
    }

    if ($reset_page_offset && isset($_GET['page'])) {
        $url = str_replace("&page=".$_GET['page'], "", $url);
        $url = str_replace("page=".$_GET['page']."&", "", $url);
        $url = str_replace("?page=".$_GET['page'], "", $url);
    }

    return $url;
}
?>