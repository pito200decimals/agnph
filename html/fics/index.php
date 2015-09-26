<?php
// Page for viewing the index page of the fics section.

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."forums/includes/functions.php");

// Fetch center content.
$vars['welcome_message'] = GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, "");
// Get news stories.
$boardName = GetSiteSetting(FICS_NEWS_SOURCE_BOARD_NAME, null);
if ($boardName != null && sql_query_into($result, "SELECT BoardId FROM ".FORUMS_BOARD_TABLE." WHERE UPPER(Name)=UPPER('".sql_escape($boardName)."');", 1)) {
    $news_board_id = $result->fetch_assoc()['BoardId'];
    $news = array();
    if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE IsThread=1 AND ParentId=$news_board_id AND NewsPost=1 ORDER BY PostDate DESC;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $row['date'] = FormatDate($row['PostDate'], FICS_DATE_FORMAT);
            $news[] = $row;
        }
    }
    if (sizeof($news) > 0) {
        $news[0]['mobile'] = true;
    }
    InitPosters($news);
    $vars['news'] = $news;
}

// Fetch left sidepanel data.
$vars['events'] = true;

// Fetch right sidepanel data.
// Get featured stories.
if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE Featured IN ('F','G','S','Z') ORDER BY Featured ASC, DateUpdated DESC LIMIT ".FICS_MAX_FEATURED_STORIES.";", 1)) {
    $featured = array();
    while ($story = $result->fetch_assoc()) {
        FillStoryInfo($story);
        $story['shortSummary'] = ShortSummary($story['Summary']);
        $featured[] = $story;
    }
    $vars['featured'] = $featured;
}
// Get random stories.
$num_rand = GetSiteSetting(FICS_NUM_RANDOM_STORIES_KEY, DEFAULT_FICS_NUM_RANDOM_STORIES);
if (is_numeric($num_rand)) {
    $num_rand = (int)$num_rand;
    if ($num_rand > FICS_MAX_NUM_RANDOM_STORIES) $num_rand = FICS_MAX_NUM_RANDOM_STORIES;
    if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." ORDER BY RAND() LIMIT $num_rand;", 1)) {
        $rand = array();
        while ($story = $result->fetch_assoc()) {
            FillStoryInfo($story);
            $story['shortSummary'] = ShortSummary($story['Summary']);
            $rand[] = $story;
        }
        $vars['random_stories'] = $rand;
    }
}

RenderPage("fics/index.tpl");
return;

function ShortSummary($summary) {
    return GetSanitizedTextTruncated($summary, MAX_FICS_SHORT_SUMMARY_LEGNTH);
}

function GetSanitizedTextTruncated($text, $max_byte_size){
    $sanitized = SanitizeHTMLTags($text, "");
    while (strlen($sanitized) > $max_byte_size) {  // Use byte-size here, not mb_char size.
        $text = mb_substr($text, 0, mb_strlen($text) - 1);
        $sanitized = SanitizeHTMLTags($text."...", "");
    }
    return $sanitized;
}

?>