<?php
// Page for viewing the index page of the fics section.

//define("DEBUG", true);

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");

// Fetch center content.
$vars['welcome_message'] = GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, DEFAULT_FICS_WELCOME_MESSAGE);

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
    if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." ORDER BY RAND() LIMIT $num_rand;", $num_rand)) {
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