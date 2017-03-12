<?php
// Page for viewing the index page of the fics section.

define("PRETTY_PAGE_NAME", "Fics");

include_once("../header.php");
include_once(SITE_ROOT."includes/news/news.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/search.php");

// Fetch center content.
$vars['welcome_message'] = SanitizeHTMLTags(GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);

// Get news stories.
$boardName = GetSiteSetting(FICS_NEWS_SOURCE_BOARD_NAME_KEY, null);
$maxNewsPosts = GetSiteSetting(FICS_MAX_NEWS_POSTS_KEY, DEFAULT_FICS_MAX_NEWS_POSTS);
$vars['news'] = GetNewsPosts($boardName, null /*section*/, $maxNewsPosts);

// Fetch left sidepanel data.
$vars['events'] = SanitizeHTMLTags(GetSiteSetting(FICS_EVENTS_LIST_KEY, null), DEFAULT_ALLOWED_TAGS);

$search_clauses = GetSearchClauses("");


// Fetch right sidepanel data.
// Get featured stories.
if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE Featured IN ('F','G','S','Z') AND ($search_clauses) ORDER BY Featured ASC, DateUpdated DESC LIMIT ".FICS_MAX_FEATURED_STORIES.";", 1)) {
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
    $vars['random_stories'] = GetRandomStories($num_rand);
}
// Get recent stories.
$num_recent = GetSiteSetting(FICS_NUM_RECENT_STORIES_KEY, DEFAULT_FICS_NUM_RECENT_STORIES);
if (is_numeric($num_recent)) {
    $num_recent = (int)$num_recent;
    if ($num_recent > FICS_MAX_NUM_RECENT_STORIES) $num_recent = FICS_MAX_NUM_RECENT_STORIES;
    if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE ($search_clauses) ORDER BY DateUpdated DESC LIMIT $num_recent;", 1)) {
        $rand = array();
        while ($story = $result->fetch_assoc()) {
            FillStoryInfo($story);
            $story['shortSummary'] = ShortSummary($story['Summary']);
            $rand[] = $story;
        }
        $vars['recent_stories'] = $rand;
    }
}
$vars['user_activity'] = GetUserActivityStats();

RenderPage("fics/index.tpl");
return;

function ShortSummary($summary) {
    return GetSanitizedTextTruncated($summary, NO_HTML_TAGS, MAX_FICS_SHORT_SUMMARY_LEGNTH, true);
}

function GetRandomStories($num_stories) {
    if (!sql_query_into($result, "SELECT StoryId FROM ".FICS_STORY_TABLE." WHERE ApprovalStatus='A' ORDER BY StoryId DESC LIMIT 1;", 1)) return array();
    $max_sid = $result->fetch_assoc()['StoryId'];
    $ret_sids = array();
    for ($i = 0; $i < $num_stories; $i++) {
        $sid = rand(1, $max_sid);
        if (in_array($sid, $ret_sids)) continue;
        $ret_sids[] = $sid;
    }
    $joined = implode(",", $ret_sids);
    $ret_stories = array();
    if (!sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE ApprovalStatus='A' AND StoryId IN ($joined);", 1)) return array();
    while ($story = $result->fetch_assoc()) {
        FillStoryInfo($story);
        $story['shortSummary'] = ShortSummary($story['Summary']);
        $ret_stories[] = $story;
    }
    return $ret_stories;
}

?>