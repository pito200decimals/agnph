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
    } else if (mb_strtolower($search) == "status:underage") {
        $now = time();
        $threshold = (new DateTime("@$now"))->format("Y-m-d");
        $year = substr($threshold, 0, 4);
        $date = substr($threshold, 4);
        $year = ((int)$year) - 18;
        $threshold = $year.$date;
        $search_clause = "DOB > '$threshold'";
    } else {
        $escaped_search = sql_escape($search);
        $search_clause = "UPPER(DisplayName) LIKE UPPER('%$escaped_search%') AND Usermode=1";
    }
} else {
    $search_clause = "Usermode=1";
}
if (HIDE_IMPORTED_ACCOUNTS_FROM_USER_LIST) {
    $search_clause .= " AND RegisterIP<>''";
}

if (isset($_GET['sort'])) {
    $order_asc = true;
    if (isset($_GET['order'])) {
        if (mb_strtolower($_GET['order']) == "asc") {
            $order_asc = true;
        } else if (mb_strtolower($_GET['order']) == "desc") {
            $order_asc = false;
        }
    }
    $order = ($order_asc ? "ASC" : "DESC");
    switch (mb_strtolower($_GET['sort'])) {
        case "status":
            $order_clause = "LastVisitTime $order, DisplayName $order";
            break;
        case "name":
            $order_clause = "DisplayName $order";
            break;
        case "position":
            $order_clause = "CHAR_LENGTH(Permissions) $order, CASE WHEN UserName LIKE '".IMPORTED_ACCOUNT_USERNAME_PREFIX."%' THEN 0 ELSE 1 END $order, DisplayName $order";
            break;
        case "register":
            $order_clause = "JoinTime $order, DisplayName $order";
            break;
        default:
            $order_clause = "DisplayName $order";
            break;
    }
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
    if ($account['LastVisitTime'] + CONSIDERED_ONLINE_DURATION > $now && !$account['HideOnlineStatus']) {
        $account['online'] = true;
    }
    $account['administrator'] = (strlen($account['Permissions']) > 0);
    $account['inactive'] = startsWith($account['UserName'], IMPORTED_ACCOUNT_USERNAME_PREFIX);
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