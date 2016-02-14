<?php
// Page for fetching the RSS xml feed.
// URL: /fics/rss.xml

// Custom includes.
define("SITE_ROOT", "../");
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."fics/includes/functions.php");

// Set up twig.
include_once(SITE_ROOT."../lib/Twig/Autoloader.php");
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(array(SITE_ROOT."skin/".BASE_SKIN."/"));
$twig = new Twig_Environment($loader, array(
    "cache" => SITE_ROOT."skin_template_cache",
));

header('Content-type: text/xml; charset=utf-8');

$stories = array();
if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE ApprovalStatus='A' ORDER BY DateUpdated DESC, StoryId DESC LIMIT ".FICS_RSS_NUM_ITEMS.";", 0)) {
    while ($story = $result->fetch_assoc()) {
        // Get pub date before story data updated with formatted date.
        $story['pubDate'] = date("D, d M Y H:i:s O", $story['DateUpdated']);
        FillStoryInfo($story);
        $chapters = GetChaptersInfo($story['StoryId']);
        $story['last_chapter'] = $chapters[sizeof($chapters) - 1];
        $stories[] = $story;
    }
}

$vars = array(
    'stories' => $stories
);

RenderPage("fics/rss.tpl");
return;
?>