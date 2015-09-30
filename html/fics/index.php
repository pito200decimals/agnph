<?php
// Page for viewing the index page of the fics section.

include_once("../header.php");
include_once(SITE_ROOT."includes/news/news.php");
include_once(SITE_ROOT."fics/includes/functions.php");

// Fetch center content.
$vars['welcome_message'] = SanitizeHTMLTags(GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);

// Get news stories.
$boardName = GetSiteSetting(FICS_NEWS_SOURCE_BOARD_NAME_KEY, null);
$vars['news'] = GetNewsPosts($boardName);

// Fetch left sidepanel data.
$vars['events'] = SanitizeHTMLTags(GetSiteSetting(FICS_EVENTS_LIST_KEY, null), DEFAULT_ALLOWED_TAGS);

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