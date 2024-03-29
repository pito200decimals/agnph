<?php
// User account listing page.
// URL: /user/list/
// URL: /user/list.php

define("PRETTY_PAGE_NAME", "User list");

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $admin_search = isset($user) && CanUserAdminSearchUsers($user);
    $match = array();
    if ($admin_search && mb_strtolower($search, "UTF-8") == "status:banned") {
        $search_clause = "Usermode=-1";
    } else if ($admin_search && mb_strtolower($search, "UTF-8") == "status:underage") {
        $now = time();
        $threshold = (new DateTime("@$now"))->format("Y-m-d");
        $year = substr($threshold, 0, 4);
        $date = substr($threshold, 4);
        $year = ((int)$year) - 18;
        $threshold = $year.$date;
        $search_clause = "DOB > '$threshold' AND (Usermode=1 OR Usermode=-1)";
    } else if ($admin_search && preg_match("/^(\d+\.\d+\.\d+\.\d+)$/", $search, $match)) {
        $ip = $match[1];
        $escaped_ip = sql_escape($ip);
        $search_clause = "(RegisterIP='$escaped_ip' OR KnownIPs LIKE ('%$escaped_ip%')) AND Usermode=1";
    } else {
        $escaped_search = sql_escape($search);
        $search_clause = "UPPER(DisplayName) LIKE UPPER('%$escaped_search%') AND Usermode=1";
    }
} else {
    $search_clause = "Usermode=1";
}
if (HIDE_IMPORTED_ACCOUNTS_FROM_USER_LIST) {
    $search_clause .= " AND ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION;
}
$search_clause = "WHERE $search_clause";

$accounts = array();
//CollectItems(USER_TABLE, "$search_clause ORDER BY ".GetQueryOrder(), $accounts, USERS_LIST_ITEMS_PER_PAGE, $iterator, "No users found.");
$sql_order = "$search_clause ORDER BY ".GetQueryOrder();
CollectItemsComplex(USER_TABLE,
    "SELECT * FROM ".USER_TABLE." T
    LEFT JOIN ".USER_VISIT_TABLE." V ON CAST(T.UserId AS CHAR)=V.GuestId
    $sql_order",
    "SELECT count(*) as ListSize FROM ".USER_TABLE." T
    LEFT JOIN ".USER_VISIT_TABLE." V ON CAST(T.UserId AS CHAR)=V.GuestId
    $sql_order",
    $accounts, USERS_LIST_ITEMS_PER_PAGE, $iterator, "No users found.");

foreach ($accounts as &$account) {
    $now = time();
    $account['dateJoined'] = FormatDate($account['JoinTime'], USERLIST_DATE_FORMAT);
    if (!$account['HideOnlineStatus']) {
        if ($account['VisitTime'] > $now - CONSIDERED_ONLINE_DURATION ||
            $account['LastVisitTime'] > $now - CONSIDERED_ONLINE_DURATION) {
            $account['online'] = true;
            $account['viewingPage'] = $account['PageName'];
        }
    }
    $account['administrator'] = (strlen($account['Permissions']) > 0);
    $account['inactive'] = startsWith($account['UserName'], IMPORTED_ACCOUNT_USERNAME_PREFIX);
    $account['banned'] = ($account['Usermode'] == -1);
    $account['avatarURL'] = GetAvatarURL($account);
    $account['hasAvatar'] = !($account['AvatarPostId'] == -1 && $account['AvatarFname'] == "");
}

$vars['users'] = $accounts;
$vars['search'] = $search;
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];
$vars['iterator'] = $iterator;
// Get column sort URL's. Resets page offset.
$vars['statusSortUrl'] = GetURLForSortOrder("status", "desc");
$vars['nameSortUrl'] = GetURLForSortOrder("name", "desc");
$vars['positionSortUrl'] = GetURLForSortOrder("position", "desc");
$vars['registerSortUrl'] = GetURLForSortOrder("register", "desc");

// This is how to output the template.
RenderPage("user/list.tpl");
return;

function OnlineStatusOrder($order) {
    // First sort by "online" vs "offline", then by last visit time, then by name.
    // Avoids situations where users marked as "don't show online" show up above online users.
    $now = time();
    $cutoff_time = $now - CONSIDERED_ONLINE_DURATION;
    return "(CASE WHEN (VisitTime > $cutoff_time OR LastVisitTime >= $cutoff_time) AND HideOnlineStatus=0 THEN 1 ELSE 0 END) $order, VisitTime $order, LastVisitTime $order";
}

function GetQueryOrder() {
    $result = GetSortClausesList(function($key, $order_asc) {
        $order = ($order_asc ? "ASC" : "DESC");
        switch ($key) {
            case "status":
                return OnlineStatusOrder($order);
            case "name":
                return "UPPER(DisplayName) $order";
                break;
            case "position":
                return "CHAR_LENGTH(Permissions) $order, CASE WHEN UserName LIKE '".IMPORTED_ACCOUNT_USERNAME_PREFIX."%' THEN 0 ELSE 1 END $order";
                break;
            case "register":
                return "JoinTime $order";
        }
        return null;
    });
    $result[] = OnlineStatusOrder("DESC");
    return implode(", ", $result);
}
?>