<?php
// Page for displaying the browse index of stories. Also used for search.
// URL: /fics/browse/{offset}/
// URL: /fics/storyindex.php?offset={item-offset}

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/file.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");

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

// TODO: Get search filters.
$search_clauses = "TRUE";

if (!sql_query_into($result,
   "SELECT * FROM ".FICS_STORY_TABLE."
    WHERE
    $search_clauses
    ORDER BY DateUpdated DESC, StoryId DESC;", 0)) RenderErrorPage("No stories found.");
$stories = array();
while ($row = $result->fetch_assoc()) {
    FillStoryInfo($row);
    $stories[] = $row;
}
if (sizeof($stories) <= $stories_per_page) {
    $iterator = "";
} else {
    $iterator = CreatePostIterator($stories, $offset, $stories_per_page);
}
$vars['stories'] = $stories;
$vars['iterator'] = $iterator;

RenderPage("fics/storyindex.tpl");
return;

function CreatePostIterator(&$stories, $offset, $stories_per_page) {
    $iterator = Paginate($stories, $offset, $stories_per_page,
        function($index, $current_page, $max_page) use ($stories_per_page) {
            $url_from_page = function ($index) use ($stories_per_page) {
                $offset = ($index - 1) * $stories_per_page;
                $url = "/fics/browse/?offset=$offset";
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
                return "[$index]";  // No link.
            } else {
                    $url = $url_from_page($index);
                return "<a href='$url'>$index</a>";
            }
        }, true);
    return $iterator;
}
?>