<?php
// Page for displaying and searching a list of tags.
// Assumes that the header is already set up, and the template will be included.
// This file just sets up the data.

// Assumes a constant named TABLE defines the tag table and TAGS_PER_PAGE defines how many elements to show per page.
// If a constant named TAG_ITEM_TABLE is defined, also fetches the counts of each tag.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label) and optionally $search_clause as the WHERE search clause.

include_once(SITE_ROOT."includes/util/listview.php");

$clauseArray = array();
if (isset($_GET['prefix'])) {
    $prefix = mb_strtolower($_GET['prefix']);
    $clauses = explode(" ", $prefix);
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
$clauseArray[] = "EXISTS(SELECT 1 FROM ".TAG_ITEM_TABLE." P WHERE T.TagId=P.TagId)";
$search_clause = "WHERE ".implode(" AND ", $clauseArray);

if (!isset($search_clause)) $search_clause = "";
if (!isset($prefix)) $prefix = "";

$tags = array();
CollectItems(TABLE, "$search_clause ORDER BY Name ASC", $tags, TAGS_PER_PAGE, $iterator, function($i) use ($prefix) {
    if (mb_strlen($prefix) > 0) {
        $escaped_prefix = urlencode($prefix);
        return strtok($_SERVER["REQUEST_URI"],'?')."?prefix=$escaped_prefix&page=$i";
    } else {
        return strtok($_SERVER["REQUEST_URI"],'?')."?page=$i";
    }
}, "No tags found.");

if (sizeof($tags) > 0) {
    foreach ($tags as &$tag) {
        $tag['typeName'] = $TAG_TYPE_MAP[$tag['Type']];
        $tag['typeClass'] = mb_strtolower($tag['Type'])."typetag tagname";
        // Get counts.
    }
    if (defined("TAG_ITEM_TABLE")) {
        $tags_by_id = array();
        foreach ($tags as &$tag) {
            $tag['tagCounts'] = 0;
            $tags_by_id[$tag['TagId']] = &$tag;
        }
        $joinedTagIds = implode(",", array_map(function($tag) { return $tag['TagId']; }, $tags));
        if (sql_query_into($result, "SELECT TagId, count(TagId) FROM ".TAG_ITEM_TABLE." WHERE TagId IN ($joinedTagIds) GROUP BY TagId;", 1)) {
            while ($row = $result->fetch_assoc()) {
                $tags_by_id[$row['TagId']]['tagCounts'] = $row['count(TagId)'];
            }
        }
    }
}

$vars['tags'] = $tags;
$vars['searchPrefix'] = $prefix;
$vars['iterator'] = $iterator;

?>