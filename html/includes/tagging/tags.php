<?php
// Page for displaying and searching a list of tags.
// Assumes that the header is already set up, and the template will be included.
// This file just sets up the data.

// Assumes a constant named TABLE defines the tag table and TAGS_PER_PAGE defines how many elements to show per page.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label) and optionally $search_clause as the WHERE search clause.

include_once(SITE_ROOT."includes/util/listview.php");

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
    }
}

$vars['tags'] = $tags;
$vars['searchPrefix'] = $prefix;
$vars['iterator'] = $iterator;

?>