<?php
// Page for displaying the browse index of stories. Also used for search.
// URL: /fics/browse/{offset}/
// URL: /fics/storyindex.php?offset={item-offset}

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."fics/includes/search.php");

if (!isset($_GET['offset']) || !is_numeric($_GET['offset'])) {
    $offset = 0;
} else {
    $offset = $_GET['offset'];
}
$escaped_offset = sql_escape($offset);
if (isset($user)) {
    $stories_per_page = $user['FicsStoriesPerPage'];
} else {
    $stories_per_page = DEFAULT_FICS_STORIES_PER_PAGE;
}

// Get search filters.
$search_clauses = "TRUE";
$order_clauses = "";
if (isset($_GET['search'])) {
    $search_terms = $_GET['search'];
    $search_clauses = GetSearchClauses($search_terms);
    $order_clauses = GetOrderingClauses($search_terms);
    if (mb_strlen($order_clauses) != 0) $order_clauses .= ", ";
} else {
    $search_terms = "";
    $search_clauses = GetSearchClauses("");
}
$vars['searchTerms'] = $search_terms;

$stories = array();
if (sql_query_into($result,
   "SELECT * FROM ".FICS_STORY_TABLE." T
    WHERE
    $search_clauses
    ORDER BY $order_clauses DateUpdated DESC, StoryId DESC;", 0)) {
    while ($story = $result->fetch_assoc()) {
        $stories[] = $story;
    }
}
if (sizeof($stories) <= $stories_per_page) {
    $iterator = "";
} else {
    $iterator = CreatePostIterator($stories, $offset, $stories_per_page, $search_terms);
}
// Only fetch detailed story data on stories we will display.
foreach ($stories as &$story) {
    FillStoryInfo($story);
}
$vars['stories'] = $stories;
$vars['iterator'] = $iterator;

RenderPage("fics/storyindex.tpl");
return;

function CreatePostIterator(&$stories, $offset, $stories_per_page, $searchterms) {
    Paginate($stories, $offset, $stories_per_page, $curr_page, $maxpage);
    $url_fn = function($i) use ($stories_per_page, $searchterms) {
            $page_offset = ($i - 1) * $stories_per_page;
            $args = array();
            if (mb_strlen($searchterms) > 0) $args[] = "search=".urlencode($searchterms);
            if ($i != 1) $args[] = "offset=$page_offset";
            if (sizeof($args) > 0) $args = "?".implode("&", $args);
            else $args = "";
            return "/fics/browse/$args";
        };
    $iterator = ConstructDefaultPageIterator($curr_page, $maxpage, DEFAULT_PAGE_ITERATOR_SIZE, $url_fn);
    $iterator_mobile = ConstructDefaultPageIterator($curr_page, $maxpage, DEFAULT_MOBILE_PAGE_ITERATOR_SIZE, $url_fn);
    return "<span class='desktop-only'>$iterator</span><span class='mobile-only'>$iterator_mobile</span>";
}
?>