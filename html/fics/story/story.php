<?php
// Page for displaying a story summary and TOC.
// URL: /fics/story/{story-id}/
// URL: /fics/story/story.php?sid={story-id}

include_once("../../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/file.php");

if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) RenderErrorPage("Invalid URL.");

$sid = $_GET['sid'];
$story = GetStory($sid) or RenderErrorPage("Story not found.");
$vars['story'] = $story;

$chapters = GetChaptersInfo($sid);// or RenderErrorPage("Story not found.");
$vars['chapters'] = $chapters;

RenderPage("fics/story/story.tpl");
return;
?>