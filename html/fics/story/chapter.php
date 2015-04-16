<?php
// Page for displaying a chapter.
// URL: /fics/story/{story-id}/{chapter-num}/
// URL: /fics/story/story.php?sid={story-id}&chapter={chapter-num}

include_once("../header.php");
include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."fics/includes/file.php");

RenderPage("fics/base.tpl");
return;
?>