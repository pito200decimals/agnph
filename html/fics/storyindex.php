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
    Paginate($stories, $offset, $stories_per_page, $curr_page, $num_max_pages);
    $iterator = ConstructPageIterator($curr_page, $num_max_pages, DEFAULT_PAGE_ITERATOR_SIZE,
        function($i, $current_page) use ($stories_per_page, $searchterms, $num_max_pages) {
            if ($i == 0) {
                if ($current_page == 1) {
                    return "<span class='currentpage'>&lt;&lt;</span>";
                } else {
                    $txt = "&lt;&lt;";
                    $i = $current_page - 1;
                }
            } else if ($i == $num_max_pages + 1) {
                if ($current_page == $num_max_pages) {
                    return "<span class='currentpage'>&gt;&gt;</span>";
                } else {
                    $txt = "&gt;&gt;";
                    $i = $current_page + 1;
                }
            } else if ($i == $current_page) {
                return "<span class='currentpage'>$i</span>";
            } else {
                $txt = $i;
            }
            $page_offset = ($i - 1) * $stories_per_page;
            if (mb_strlen($searchterms) > 0) {
                if ($i != 1) {
                    $url = "/fics/browse/?search=".urlencode($searchterms)."&offset=$page_offset";
                } else {
                    $url = "/fics/browse/?search=".urlencode($searchterms);
                }
            } else {
                if ($i != 1) {
                    $url = "/fics/browse/?offset=$page_offset";
                } else {
                    $url = "/fics/browse/";
                }
            }
            return "<a href='$url'>$txt</a>";
        }, true);
    return $iterator;
}
?>