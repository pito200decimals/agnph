<?php
// Site home page.

// Site includes, including login authentication.
include_once("header.php");
include_once(SITE_ROOT."includes/news/news.php");

// Get welcome message.
$vars['welcome_message'] = SanitizeHTMLTags(GetSiteSetting(SITE_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);

// Get recent news posts.
$all_news = array();
AddNews($all_news, SITE_NEWS_SOURCE_BOARD_NAME_KEY, "Site");
AddNews($all_news, FORUMS_NEWS_SOURCE_BOARD_NAME_KEY, "Forums");
AddNews($all_news, GALLERY_NEWS_SOURCE_BOARD_NAME_KEY, "Gallery");
AddNews($all_news, FICS_NEWS_SOURCE_BOARD_NAME_KEY, "Fics");
AddNews($all_news, OEKAKI_NEWS_SOURCE_BOARD_NAME_KEY, "Oekaki");

usort($all_news, function($n1, $n2) { return $n1['PostDate'] - $n2['PostDate']; });
$all_news = array_reverse($all_news);
$num_max_news = GetSiteSetting(MAX_SITE_NEWS_POSTS_KEY, DEFAULT_MAX_SITE_NEWS_POSTS);
$vars['news'] = array_slice($all_news, 0, $num_max_news);

RenderPage("index.tpl");
return;

function AddNews(&$vals, $key, $label) {
    $value = GetNewsPosts(GetSiteSetting($key, null), $label);
    if ($value != null) {
        $vals = array_merge($vals, $value);
    }
}
?>