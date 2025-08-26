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
include_once(SITE_ROOT."vendor/autoload.php");
//Twig_Autoloader::register();
$loader = new \Twig\Loader\FilesystemLoader(array(SITE_ROOT."skin/".BASE_SKIN."/"));
$twig = new \Twig\Environment($loader, array(
    "cache" => SITE_ROOT."skin_template_cache",
));

header('Content-type: text/xml; charset=utf-8');

$lastUpdateDate = null;
$stories = array();
if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE ApprovalStatus='A' ORDER BY DateUpdated DESC, StoryId DESC LIMIT ".FICS_RSS_NUM_ITEMS.";", 0)) {
    while ($story = $result->fetch_assoc()) {
        // Get pub date before story data updated with formatted date.
        $timestamp = $story['DateUpdated'];
        $date = date("D, d M Y H:i:s O", $timestamp);
        $story['pubDate'] = $date;
        if ($lastUpdateDate == null) $lastUpdateDate = $date;
        FillStoryInfo($story);
        $chapters = GetChaptersInfo($story['StoryId']);
        $story['last_chapter'] = $chapters[sizeof($chapters) - 1];
        $story['updateGuid'] = $story['StoryId']."-".sizeof($chapters)."-".md5($timestamp);
        $story['lastChapterNum'] = sizeof($chapters);
        $stories[] = $story;
    }
}

$vars = array(
    'stories' => $stories
);
if ($lastUpdateDate != null) {
    $vars['lastUpdateDate'] = $lastUpdateDate;
}

RenderPage("fics/rss.tpl");
return;
?>
