<?php
// Page for displaying a list of fics authors.
// URL: /fics/authors/?page={page}

include_once("../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");

$search_clause = "WHERE TRUE";
if (isset($_GET['search'])) {
    $search = mb_strtolower($_GET['search'], "UTF-8");
    $escaped_prefix = sql_escape($search);
    $search_clause = "WHERE LOWER(DisplayName) LIKE '$escaped_prefix%'";
} else {
    $search = "";
}
// TODO: Include co-authors?
$search_clause .= " AND S.ApprovalStatus<>'D'";

include_once(SITE_ROOT."includes/util/listview.php");

$authors = array();
CollectItemsComplex(
    USER_TABLE,
    "SELECT T.*, COUNT(StoryId) as StoryCount FROM ".USER_TABLE." T INNER JOIN ".FICS_STORY_TABLE." S ON T.UserId=S.AuthorUserId $search_clause GROUP BY T.UserId ORDER BY ".GetQueryOrder(),
    "SELECT COUNT(*) AS ListSize FROM (SELECT T.*, COUNT(StoryId) as StoryCount FROM ".USER_TABLE." T INNER JOIN ".FICS_STORY_TABLE." S ON T.UserId=S.AuthorUserId $search_clause GROUP BY T.UserId) T2",
    $authors,
    FICS_LIST_ITEMS_PER_PAGE,
    $iterator,
    "No authors found.");

$vars['authors'] = $authors;
$vars['author_search'] = $search;
$vars['iterator'] = $iterator;

$vars['nameSortUrl'] = GetURLForSortOrder("name", "asc");
$vars['countSortUrl'] = GetURLForSortOrder("count", "desc");
if (isset($_GET['sort'])) $vars['sortParam'] = $_GET['sort'];
if (isset($_GET['order'])) $vars['orderParam'] = $_GET['order'];

RenderPage("fics/authorindex.tpl");
return;

function GetQueryOrder() {
    $result = GetSortClausesList(function($key, $order_asc) {
        $order = ($order_asc ? "ASC" : "DESC");
        switch ($key) {
            case "name":
                return "UPPER(DisplayName) $order";
            case "count":
                return "StoryCount $order";
        }
        return null;
    });
    $result[] = "UPPER(DisplayName) ASC";
    return implode(", ", $result);
}

?>