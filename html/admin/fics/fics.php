<?php
// Main control panel for admin operations.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminFics']) {
    DoRedirect();
}

if (isset($_POST['submit'])) {
    HandlePost();
    PostSessionBanner("Settings changed", "green");
    Redirect($_SERVER['REQUEST_URI']);
}

$vars['welcome_message'] = SanitizeHTMLTags(GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);
$vars['min_word_count'] = GetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, null);
$vars['news_posts_board'] = GetSiteSetting(FICS_NEWS_SOURCE_BOARD_NAME_KEY, null);
$vars['num_rand_stories'] = GetSiteSetting(FICS_NUM_RANDOM_STORIES_KEY, "0");
$vars['events_list'] = GetSiteSetting(FICS_EVENTS_LIST_KEY, null);

$vars['admin_section'] = "fics";
RenderPage("admin/fics/fics.tpl");
return;


function HandlePost() {
    if (isset($_POST['welcome-message'])) {
        $msg = SanitizeHTMLTags($_POST['welcome-message'], DEFAULT_ALLOWED_TAGS);
        if ($msg != GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, "")) {
            if (mb_strlen(SanitizeHTMLTags($msg, "")) == 0) {
                $msg = "";
            }
            SetSiteSetting(FICS_WELCOME_MESSAGE_KEY, $msg);
        }
    }
    if (isset($_POST['min-word-count'])) {
        if (is_numeric($_POST['min-word-count']) &&
            ((int)$_POST['min-word-count']) >= 0) {
            $val = (int)$_POST['min-word-count'];
            SetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, $val);
        } else {
            PostSessionBanner("Invalid minimum word count", "red");
        }
    }
    if (isset($_POST['news-posts-board'])) {
        $board_name = $_POST['news-posts-board'];
        if ($board_name != GetSiteSetting(FICS_NEWS_SOURCE_BOARD_NAME_KEY, null)) {
            $escaped_board_name = sql_escape($board_name);
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE UPPER(Name)=UPPER('$escaped_board_name');", 1)) {
                SetSiteSetting(FICS_NEWS_SOURCE_BOARD_NAME_KEY, $board_name);
            } else {
                PostSessionBanner("Board not found", "red");
            }
        }
    }
    if (isset($_POST['num-rand-stories'])) {
        if (is_numeric($_POST['num-rand-stories']) &&
            ((int)$_POST['num-rand-stories']) >= 0) {
            $val = (int)$_POST['num-rand-stories'];
            if ($val > FICS_MAX_NUM_RANDOM_STORIES) $val = FICS_MAX_NUM_RANDOM_STORIES;
            SetSiteSetting(FICS_NUM_RANDOM_STORIES_KEY, $val);
        } else {
            PostSessionBanner("Invalid number of random stories", "red");
        }
    }
    if (isset($_POST['events'])) {
        $msg = SanitizeHTMLTags($_POST['events'], DEFAULT_ALLOWED_TAGS);
        if ($msg != GetSiteSetting(FICS_EVENTS_LIST_KEY, "")) {
            if (mb_strlen(SanitizeHTMLTags($msg, "")) == 0) {
                $msg = "";
            }
            SetSiteSetting(FICS_EVENTS_LIST_KEY, $msg);
        }
    }
}

?>