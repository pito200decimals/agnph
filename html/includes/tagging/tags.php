<?php
// Page for displaying and searching a list of tags.
// Assumes that the header is already set up, and the template will be included.
// This file just sets up the data.

// Assumes a constant named TABLE defines the tag table and TAGS_PER_PAGE defines how many elements to show per page.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label) and optionally $search_clause as the WHERE search clause.
// Also assume the alias table is named ALIAS_TABLE

include_once(SITE_ROOT."includes/util/listview.php");

$clauseArray = array();
if (isset($_GET['search'])) {
    $search = mb_strtolower($_GET['search'], "UTF-8");
    $clauses = explode(" ", $search);
    foreach ($clauses as $clause) {
        $isTypeSearch = false;
        $isExactSearch = false;
        foreach ($TAG_TYPE_MAP as $char => $name) {
            $lower_name = strtolower($name);
            if ($clause == "type:$lower_name") {
                $isTypeSearch = true;
                $clauseArray[] = "(Type='$char')";
            }
        }
        if (preg_match("/^\".*\"$/", $clause)) {
            $escaped_name = sql_escape(mb_substr($clause, 1, mb_strlen($clause) - 2));
            $isExactSearch = true;
            $clauseArray[] = "(LOWER(Name)='$escaped_name')";
        }
        if (!$isTypeSearch && !$isExactSearch) {
            $escaped_prefix = sql_escape($clause);
            $clauseArray[] = "(LOWER(Name) LIKE '$escaped_prefix%')";
        }
    }
}
// Only include tags that have at least one item.
$clauseArray[] = "T.ItemCount > 0";
if (!$is_api) {
    // Only include tags that have not been aliased.
    $clauseArray[] = "NOT(EXISTS(SELECT 1 FROM ".ALIAS_TABLE." A WHERE A.TagId=T.TagId))";
}
$search_clause = "WHERE ".implode(" AND ", $clauseArray);

if (!isset($search_clause)) $search_clause = "";
if (!isset($search)) $search = "";

$tags = array();
CollectItems(TABLE, "$search_clause ORDER BY ".GetQueryOrder(), $tags, TAGS_PER_PAGE, $iterator, "No tags found.");

$alias_mapping = array();
if ($is_api) {
    $alias_ids_to_fetch = array_map(function($tag) { return $tag['TagId']; }, $tags);
    $joined_ids = implode(",", $alias_ids_to_fetch);
    if (sql_query_into($result, "SELECT Q.TagId AS TagId, T.Name AS AliasName FROM ".TABLE." T INNER JOIN ".ALIAS_TABLE." Q ON Q.AliasTagId=T.TagId WHERE Q.TagId IN ($joined_ids);", 1)) {
        while ($row = $result->fetch_assoc()) {
            $alias_mapping[$row['TagId']] = $row['AliasName'];
        }
    }
}

foreach ($tags as &$tag) {
    $tag['displayName'] = TagNameToDisplayName($tag['Name']);
    $tag['quotedName'] = contains($tag['Name'], ":") ? "\"".$tag['Name']."\"" : $tag['Name'];
    $tag['tagCounts'] = $tag['ItemCount'];
    $tag['typeName'] = $TAG_TYPE_MAP[$tag['Type']];
    $tag['typeClass'] = mb_strtolower($tag['Type'], "UTF-8")."typetag tagname";
    if (isset($alias_mapping[$tag['TagId']])) {
        $tag['alias'] = $alias_mapping[$tag['TagId']];
    }
}

$vars['tags'] = $tags;
$vars['tag_search'] = $search;
$vars['iterator'] = $iterator;

$vars['nameSortUrl'] = GetURLForSortOrder("name", "asc");
$vars['typeSortUrl'] = GetURLForSortOrder("type", "asc");
$vars['countSortUrl'] = GetURLForSortOrder("count", "desc");
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];

function GetQueryOrder() {
    $result = GetSortClausesList(function($key, $order_asc) {
        $order = ($order_asc ? "ASC" : "DESC");
        switch ($key) {
            case "name":
                return "Name $order";
            case "type":
                return "Type $order";
            case "count":
                return "ItemCount $order";
        }
        return null;
    });
    $result[] = "Name ASC";
    return implode(", ", $result);
}

?>