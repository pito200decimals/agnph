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
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

$vars['welcome_message'] = SanitizeHTMLTags(GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);
$vars['min_word_count'] = GetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, null);
$vars['num_rand_stories'] = GetSiteSetting(FICS_NUM_RANDOM_STORIES_KEY, "");

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
}

?>