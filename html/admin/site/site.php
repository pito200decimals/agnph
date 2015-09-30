<?php
// Main control panel for admin operations.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/date.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminSite']) {
    DoRedirect();
}
$vars['is_maintenance_mode'] = IsMaintenanceMode();

if (isset($_POST['submit'])) {
    HandlePost();
    PostSessionBanner("Settings changed", "green");
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}
$vars['site_welcome_message'] = SanitizeHTMLTags(GetSiteSetting(SITE_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);
$vars['register_message'] = SanitizeHTMLTags(GetSiteSetting(REGISTER_DISCLAIMER_KEY, ""), DEFAULT_ALLOWED_TAGS);
$vars['short_ban_duration'] = FormatShortDuration(GetSiteSetting(SHORT_BAN_DURATION_KEY, ""));
$vars['news_posts_board'] = GetSiteSetting(SITE_NEWS_SOURCE_BOARD_NAME_KEY, null);

RenderPage("admin/site/site.tpl");
return;

function HandlePost() {
    if (isset($_POST['site-welcome-message'])) {
        $msg = SanitizeHTMLTags($_POST['site-welcome-message'], DEFAULT_ALLOWED_TAGS);
        if ($msg != GetSiteSetting(SITE_WELCOME_MESSAGE_KEY, "")) {
            if (mb_strlen(SanitizeHTMLTags($msg, "")) == 0) {
                $msg = "";
            }
            SetSiteSetting(SITE_WELCOME_MESSAGE_KEY, $msg);
        }
    }
    if (isset($_POST['register-message'])) {
        $msg = SanitizeHTMLTags($_POST['register-message'], DEFAULT_ALLOWED_TAGS);
        if ($msg != GetSiteSetting(REGISTER_DISCLAIMER_KEY, "")) {
            if (mb_strlen(SanitizeHTMLTags($msg, "")) == 0) {
                $msg = "";
            }
            SetSiteSetting(REGISTER_DISCLAIMER_KEY, $msg);
        }
    }
    if (isset($_POST['short-ban-duration'])) {
        $duration = ParseShortDuration($_POST['short-ban-duration']);
        if ($duration > 0 && $duration != GetSiteSetting(SHORT_BAN_DURATION_KEY, "")) {
            SetSiteSetting(SHORT_BAN_DURATION_KEY, $duration);
        }
    }
    if (isset($_POST['maintenance-mode'])) {
        SetSiteSetting(MAINTENANCE_MODE_KEY, "true");
    } else {
        SetSiteSetting(MAINTENANCE_MODE_KEY, "false");
    }
    if (isset($_POST['news-posts-board'])) {
        $board_name = $_POST['news-posts-board'];
        if ($board_name != GetSiteSetting(SITE_NEWS_SOURCE_BOARD_NAME_KEY, null)) {
            $escaped_board_name = sql_escape($board_name);
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE UPPER(Name)=UPPER('$escaped_board_name');", 1)) {
                SetSiteSetting(SITE_NEWS_SOURCE_BOARD_NAME_KEY, $board_name);
            } else {
                PostSessionBanner("Board not found", "red");
            }
        }
    }
}
?>