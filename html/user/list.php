<?php
// User account listing page.
// URL: /user/list/
// URL: /user/list.php

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");

$order_clause = "DisplayName ASC";
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    if (mb_strtolower($search) == "status:banned") {
        $search_clause = "Usermode=-1";
    } else {
        $escaped_search = sql_escape($search);
        $search_clause = "UPPER(DisplayName) LIKE UPPER('%$escaped_search%') AND Usermode=1";
    }
} else {
    $search_clause = "Usermode=1";
}
// TODO: Hide un-recovered accounts?

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
            break;
        case "name":
            $sort = "DisplayName";
            break;
        case "position":
            $sort = "CHAR_LENGTH(Permissions)";
            break;
        case "register":
            $sort = "JoinTime";
            break;
        default:
            $sort = "DisplayName";
            break;
    }
    $order = ($order_asc ? "ASC" : "DESC");
    $order_clause = "$sort $order";
}

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
    $account['avatarURL'] = GetAvatarURL($account);
}

$vars['users'] = $accounts;
$vars['search'] = $search;
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];
$vars['iterator'] = $iterator;
// Get column sort URL's. Resets page offset.
$vars['statusSortUrl'] = GetURLForSortOrder("status", "desc");
$vars['nameSortUrl'] = GetURLForSortOrder("name", "asc");
$vars['positionSortUrl'] = GetURLForSortOrder("position", "desc");
$vars['registerSortUrl'] = GetURLForSortOrder("register", "desc");

// This is how to output the template.
RenderPage("user/list.tpl");
return;

// Gets the sorting URL when clicking column headers. Resets the pagination offset when resorting.
function GetSortURL($sort) {
    $base_sort_url = "/user/list/?";
    foreach ($_GET as $key => $value) {
        $base_sort_url .= "$key=".urlencode($value)."&";
    }
    $base_sort_url .= "sort=".urlencode($sort);
    // Okay to not use multibyte string manipulation here.
    if (isset($_GET['sort']) && strtolower($_GET['sort']) == strtolower($sort)) {
        // Same sort type, reverse direction.
        if (isset($_GET['order']) && strtolower($_GET['order']) == "desc") {
            $base_sort_url .= "&order=asc";
        } else {
            $base_sort_url .= "&order=desc";
        }
    } else if (!isset($_GET['sort'])) {
        // Different sort type, use default descending order.
        $base_sort_url .= "&order=desc";
    }
    return $base_sort_url;
}
?>