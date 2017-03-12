<?php
// Page for viewing the search index of pools.
// URL: /gallery/pools/

define("PRETTY_PAGE_NAME", "Gallery");

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");

if (isset($_GET['search'])) {
    $term = $_GET['search'];
    $escaped_term = sql_escape($term);
    $whereClause = "WHERE UPPER(Name) LIKE UPPER('%$escaped_term%')";
} else {
    $term = "";
    $whereClause = "";
}

$pools = array();
CollectItems(GALLERY_POOLS_TABLE, "$whereClause ORDER BY ".GetQueryOrder(), $pools, GALLERY_LIST_ITEMS_PER_PAGE, $iterator, "No pools found.");

if (sizeof($pools) > 0) {
    // Compute pool search names.
    foreach ($pools as &$pool) {
        $pool['searchString'] = "pool:".str_replace(" ", "_", $pool['Name']);
    }
    // Compute counts.
    foreach ($pools as &$pool) { $pool['count'] = 0; }
    $pool_ids = array_map(function($pool) {
        return $pool['PoolId'];
    }, $pools);
    $joined = implode(",", $pool_ids);
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPoolId IN ($joined);", 0) or RenderErrorPage("No pools found.");
    while ($row = $result->fetch_assoc()) {
        // Find pool with the given id.
        $pool_id = $row['ParentPoolId'];
        foreach ($pools as &$pool) {
            if ($pool['PoolId'] == $pool_id) {
                $pool['count']++;
            }
        }
    }
    // Compute creators.
    $creator_ids = array_map(function($pool) {
        return $pool['CreatorUserId'];
    }, $pools);
    $joined = implode(",", $creator_ids);
    sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId IN ($joined);", 0) or RenderErrorPage("No pools found.");
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['UserId'];
        foreach ($pools as &$pool) {
            if ($pool['CreatorUserId'] == $user_id) {
                $pool['creator'] = $row;
            }
        }
    }
}

$vars['pool_search'] = $term;
$vars['pools'] = $pools;
$vars['postIterator'] = $iterator;
$vars['nameSortUrl'] = GetURLForSortOrder("name", "asc");
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];

if (isset($user) && CanUserCreateOrDeletePools($user)) {
    $vars['canEditPools'] = true;
}
if (isset($user) && CanUserCreatePool($user)) {
    $vars['canCreatePools'] = true;
}
if (isset($user) && CanUserDeletePool($user)) {
    $vars['canDeletePools'] = true;
}
RenderPage("gallery/pools/poolindex.tpl");
return;

function GetQueryOrder() {
    $result = GetSortClausesList(function($key, $order_asc) {
        $order = ($order_asc ? "ASC" : "DESC");
        switch ($key) {
            case "name":
                return "Name $order";
            // TODO: Support more sort orders.
        }
        return null;
    });
    $result[] = "Name ASC";
    return implode(", ", $result);
}

?>