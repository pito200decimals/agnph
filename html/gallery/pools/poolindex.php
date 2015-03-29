<?php
// Page for viewing the search index of pools.
// URL: /gallery/pools/

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."gallery/includes/listview.php");


$pools = array();
CollectItems(GALLERY_POOLS_TABLE, "ORDER BY Name ASC", $pools, GALLERY_LIST_ITEMS_PER_PAGE, $iterator, function($i) {
    return "/gallery/pools/?page=$i";
}, "No pools found.");

if (sizeof($pools) > 0) {
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
}

$vars['pools'] = $pools;
$vars['postIterator'] = $iterator;
RenderPage("gallery/pools/poolindex.tpl");
return;

?>