<?php
// Page for displaying the browse index of stories. Also used for search.
// URL: /fics/browse/{offset}/
// URL: /fics/storyindex.php?offset={item-offset}

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/file.php");

if (!isset($_GET['offset']) || !is_numeric($_GET['offset'])) {
    $offset = 0;
} else {
    $offset = $_GET['offset'];
}
$escaped_offset = sql_escape($offset);
$stories_per_page = FICS_STORIES_PER_PAGE;

// TODO: Get search filters.
$search_clauses = "TRUE";

if (!sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE $search_clauses ORDER BY DateUpdated DESC, StoryId DESC LIMIT $stories_per_page OFFSET $escaped_offset;", 0)) RenderErrorPage("No stories found.");
$stories = array();
while ($row = $result->fetch_assoc()) {
    FillStoryInfo($row);
    $stories[] = $row;
}
$vars['stories'] = $stories;

RenderPage("fics/storyindex.tpl");
return;
?>