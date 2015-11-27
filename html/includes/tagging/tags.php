<?php
// Page for displaying and searching a list of tags.
// Assumes that the header is already set up, and the template will be included.
// This file just sets up the data.

// Assumes a constant named TABLE defines the tag table and TAGS_PER_PAGE defines how many elements to show per page.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label) and optionally $search_clause as the WHERE search clause.

include_once(SITE_ROOT."includes/util/listview.php");

$clauseArray = array();
if (isset($_GET['search'])) {
    $search = mb_strtolower($_GET['search'], "UTF-8");
    $clauses = explode(" ", $search);
    foreach ($clauses as $clause) {
        $isTypeSearch = false;
        foreach ($TAG_TYPE_MAP as $char => $name) {
            $lower_name = strtolower($name);
            if ($clause == "type:$lower_name") {
                $isTypeSearch = true;
                $clauseArray[] = "(Type='$char')";
            }
        }
        if (!$isTypeSearch) {
            $escaped_prefix = sql_escape($clause);
            $clauseArray[] = "(LOWER(Name) LIKE '$escaped_prefix%')";
        }
    }
}
// Only include tags that have at least one item.
$clauseArray[] = "T.ItemCount > 0";
$search_clause = "WHERE ".implode(" AND ", $clauseArray);

if (!isset($search_clause)) $search_clause = "";
if (!isset($search)) $search = "";

$tags = array();
CollectItems(TABLE, "$search_clause ORDER BY ".GetQueryOrder(true), $tags, TAGS_PER_PAGE, $iterator, "No tags found.");

foreach ($tags as &$tag) {
    $tag['displayName'] = TagNameToDisplayName($tag['Name']);
    $tag['quotedName'] = contains($tag['Name'], ":") ? "\"".$tag['Name']."\"" : $tag['Name'];
    $tag['tagCounts'] = $tag['ItemCount'];
    $tag['typeName'] = $TAG_TYPE_MAP[$tag['Type']];
    $tag['typeClass'] = mb_strtolower($tag['Type'], "UTF-8")."typetag tagname";
}

$vars['tags'] = $tags;
$vars['search'] = $search;
$vars['iterator'] = $iterator;

$vars['nameSortUrl'] = GetURLForSortOrder("name", "asc");
$vars['typeSortUrl'] = GetURLForSortOrder("type", "asc");
$vars['countSortUrl'] = GetURLForSortOrder("count", "desc");
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];

function GetQueryOrder($allow_count_order) {
    $order_clause = "Name ASC";
    $search = "";
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        if (mb_strtolower($search, "UTF-8") == "status:banned") {
            $search_clause = "Usermode=-1";
        } else {
            $escaped_search = sql_escape($search);
            $search_clause = "UPPER(DisplayName) LIKE UPPER('%$escaped_search%') AND Usermode=1";
        }
    } else {
        $search_clause = "Usermode=1";
    }

    if (isset($_GET['sort'])) {
        $order_asc = true;
        if (isset($_GET['order'])) {
            if (mb_strtolower($_GET['order'], "UTF-8") == "asc") {
                $order_asc = true;
            } else if (mb_strtolower($_GET['order'], "UTF-8") == "desc") {
                $order_asc = false;
            }
        }
        $sort = "Name";
        switch (mb_strtolower($_GET['sort'], "UTF-8")) {
            case "name":
                $sort = "Name";
                break;
            case "type":
                $sort = "Type";
                break;
            case "count":
                if ($allow_count_order) $sort = "ItemCount";
                // Otherwise, use default.
                break;
            default:
                $sort = "Name";
                break;
        }
        $order = ($order_asc ? "ASC" : "DESC");
        $order_clause = "$sort $order";
    }
    debug("Order clause: $order_clause");
    return $order_clause;
}

?>