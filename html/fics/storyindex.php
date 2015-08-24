<?php
// Page for displaying the browse index of stories. Also used for search.
// URL: /fics/browse/{offset}/
// URL: /fics/storyindex.php?offset={item-offset}

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/file.php");
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
    $vars['searchTerms'] = $search_terms;
    $search_clauses = GetSearchClauses($search_terms);
    if (mb_strlen($search_clauses) == 0) $search_clauses = "TRUE";
    $order_clauses = GetOrderingClauses($search_terms);
    if (mb_strlen($order_clauses) != 0) $order_clauses .= ", ";
} else {
    $blacklist_clauses = GetSearchClauses("");
    if (mb_strlen($blacklist_clauses) > 0) $search_clauses = $blacklist_clauses;
}

$stories = array();
if (sql_query_into($result,
   "SELECT * FROM ".FICS_STORY_TABLE." T
    WHERE
    $search_clauses
    ORDER BY $order_clauses DateUpdated DESC, StoryId DESC;", 0)) {
    while ($story = $result->fetch_assoc()) {
        FillStoryInfo($story);
        $stories[] = $story;
    }
}
if (sizeof($stories) <= $stories_per_page) {
    $iterator = "";
} else {
    if (isset($search_terms)) {
        $iterator = CreatePostIterator($stories, $offset, $stories_per_page, $search_terms);
    } else {
        $iterator = CreatePostIterator($stories, $offset, $stories_per_page, null);
    }
}
$vars['stories'] = $stories;
$vars['iterator'] = $iterator;

RenderPage("fics/storyindex.tpl");
return;

function CreatePostIterator(&$stories, $offset, $stories_per_page, $search_terms) {
    $iterator = Paginate($stories, $offset, $stories_per_page,
        function($index, $current_page, $max_page) use ($stories_per_page, $search_terms) {
            $url_from_page = function ($index) use ($stories_per_page, $search_terms) {
                $offset = ($index - 1) * $stories_per_page;
                $url = "/fics/browse/?offset=$offset";
                if ($search_terms != null) {
                    $url = "/fics/browse/?search=".urlencode($search_terms)."&offset=$offset";  // Safe with UTF-8.
                } else {
                    $url = "/fics/browse/?offset=$offset";
                }
                return $url;
            };
            if ($index == 0) {
                if ($current_page == 1) {
                    return "";  // No link.
                } else {
                    $url = $url_from_page($current_page - 1);
                    return "<a href='$url'>&lt;&lt;</a>";
                }
            } else if ($index == $max_page + 1) {
                if ($current_page == $max_page) {
                    return "";  // No link.
                } else {
                    $url = $url_from_page($current_page + 1);
                    return "<a href='$url'>&gt;&gt;</a>";
                }
            } else if ($index == $current_page) {
                return "<a>[$index]</a>";  // No link.
            } else {
                    $url = $url_from_page($index);
                return "<a href='$url'>$index</a>";
            }
        }, true);
    return $iterator;
}
?>