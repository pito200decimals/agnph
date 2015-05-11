<?php
// Page for displaying a list of fics authors.
// URL: /fics/authors/?page={page}

include_once("../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");

$search_clause = "";
if (isset($_GET['prefix'])) {
    $prefix = mb_strtolower($_GET['prefix']);
    $escaped_prefix = sql_escape($prefix);
    $search_clause = "WHERE LOWER(DisplayName) LIKE '$prefix%'";
} else {
    $prefix = "";
}

include_once(SITE_ROOT."includes/util/listview.php");

$authors = array();
CollectItems(USER_TABLE, "$search_clause ORDER BY DisplayName ASC", $authors, FICS_LIST_ITEMS_PER_PAGE, $iterator, function($i) use ($prefix) {
    if (mb_strlen($prefix) > 0) {
        $escaped_prefix = urlencode($prefix);
        return strtok($_SERVER["REQUEST_URI"],'?')."?prefix=$escaped_prefix&page=$i";
    } else {
        return strtok($_SERVER["REQUEST_URI"],'?')."?page=$i";
    }
}, "No authors found.");

$author_ids = array_map(function($author) {
    return $author['UserId'];
}, $authors);
$joined_ids = implode(",", $author_ids);

if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE AuthorUserId IN ($joined_ids);", 0)) {
    $author_id_map = array();
    foreach ($authors as &$author) {
        $author['storyCount'] = 0;
        $author_id_map[$author['UserId']] = &$author;
    }
    while ($row = $result->fetch_assoc()) {
        $author_id = $row['AuthorUserId'];
        $author_id_map[$author_id]['storyCount']++;
    }
}

$vars['authors'] = $authors;
$vars['searchPrefix'] = $prefix;
$vars['iterator'] = $iterator;

RenderPage("fics/authorindex.tpl");
return;
?>