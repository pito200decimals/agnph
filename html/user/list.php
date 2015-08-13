<?php
// User account listing page.
// URL: /user/list/
// URL: /user/list.php

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");

$search_clause = "TRUE";
$order_clause = "DisplayName ASC";
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $escaped_search = sql_escape($search);
    $search_clause = "UPPER(DisplayName) LIKE UPPER('%$escaped_search%')";
}

$reverse_order_param = "desc";
if (isset($_GET['sort'])) {
    $order_asc = true;
    if (isset($_GET['order'])) {
        if (mb_strtolower($_GET['order']) == "asc") {
            $order_asc = true;
        } else if (mb_strtolower($_GET['order']) == "desc") {
            $order_asc = false;
        }
    }
    switch (mb_strtolower($_GET['sort'])) {
        case "status":
            $sort = "LastVisitTime";
            $order_asc = !$order_asc;
            break;
        case "name":
            $sort = "DisplayName";
            break;
        case "position":
            $sort = "CHAR_LENGTH(Permissions)";
            $order_asc = !$order_asc;
            break;
        case "register":
            $sort = "JoinTime";
            break;
        default:
            $sort = "DisplayName";
            break;
    }
    $order = ($order_asc ? "ASC" : "DESC");
    $reverse_order_param = ($order_asc ? "desc" : "asc");
    $order_clause = "$sort $order";
}
$search_clause .= " AND Usermode=1";

$accounts = array();
CollectItems(USER_TABLE, "WHERE $search_clause ORDER BY $order_clause", $accounts, USERS_LIST_ITEMS_PER_PAGE, $iterator, function($i) use ($search) {
    $url = $_SERVER['REQUEST_URI'];
    if (contains($url, "?")) {
        return $url."&page=$i";
    } else {
        return $url."?page=$i";
    }
}, "No users found.");

$now = time();
foreach ($accounts as &$account) {
    $account['dateJoined'] = FormatDate($account['JoinTime'], USERLIST_DATE_FORMAT);
    if ($account['LastVisitTime'] + CONSIDERED_ONLINE_DURATION > $now) $account['online'] = true;
    $account['administrator'] = (strlen($account['Permissions']) > 0);  // TODO: Also use this field to display un-recovered accounts?
}

$vars['users'] = $accounts;
$vars['search'] = $search;
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];
$vars['iterator'] = $iterator;
// Get column sort URL's.
$vars['statusSortUrl'] = GetSortURL("status");
$vars['nameSortUrl'] = GetSortURL("name");
$vars['positionSortUrl'] = GetSortURL("position");
$vars['registerSortUrl'] = GetSortURL("register");

// This is how to output the template.
RenderPage("user/list.tpl");
return;

// Gets the sorting URL when clicking column headers. Resets the pagination offset when resorting.
function GetSortURL($sort) {
    $base_sort_url = "/user/list/?";
    if (isset($_GET['search'])) $base_sort_url .= "search=".urlencode($_GET['search'])."&";
    $base_sort_url .= "sort=".urlencode($sort);
    // Okay to not use multibyte string manipulation here.
    if (isset($_GET['sort']) && strtolower($_GET['sort']) == strtolower($sort)) {
        if (isset($_GET['order'])) {
            if (strtolower($_GET['order']) == "asc") {
                $base_sort_url .= "&order=desc";
            } else {
                $base_sort_url .= "&order=asc";
            }
        } else {
            // Currently in ascending order, move to descending order.
            $base_sort_url .= "&order=desc";
        }
    } else if (!isset($_GET['sort']) && $sort == "register") {
        $base_sort_url .= "&order=desc";
    }
    return $base_sort_url;
}
?>