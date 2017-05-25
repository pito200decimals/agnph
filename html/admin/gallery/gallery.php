<?php
// Main control panel for admin operations.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminGallery']) {
    DoRedirect();
}

if (isset($_POST['submit'])) {
    HandlePost();
    PostSessionBanner("Settings changed", "green");
    Redirect($_SERVER['REQUEST_URI']);
}

$vars['news_posts_board'] = GetSiteSetting(GALLERY_NEWS_SOURCE_BOARD_NAME_KEY, null);
$vars['flag_reasons'] = GetSiteSettingArray('gallery_flag_reasons');

$vars['admin_section'] = "gallery";
RenderPage("admin/gallery/gallery.tpl");
return;


function HandlePost() {
    if (isset($_POST['news-posts-board'])) {
        $board_name = $_POST['news-posts-board'];
        if ($board_name != GetSiteSetting(GALLERY_NEWS_SOURCE_BOARD_NAME_KEY, null)) {
            $escaped_board_name = sql_escape($board_name);
            if (sql_query_into($result, "SELECT * FROM ".FORUMS_BOARD_TABLE." WHERE UPPER(Name)=UPPER('$escaped_board_name');", 1)) {
                SetSiteSetting(GALLERY_NEWS_SOURCE_BOARD_NAME_KEY, $board_name);
            } else {
                PostSessionBanner("Board not found", "red");
            }
        }
    }

    // Update flag reasons
    if (isset($_POST['flag_reason'])) {
        $reasons = $_POST['flag_reason'];
        if (sizeof($reasons) > 0) {
            $validated = true;
            foreach ($reasons as &$reason) {
                if (contains($reason, "#") && !endsWith($reason, "#")) {
                    PostSessionBanner("Invalid flag reason: $reason", "red");
                    $validated = false;
                }
            }
            if ($validated) {
                SetSiteSettingArray('gallery_flag_reasons', $reasons);
            }
        }
    }
}

?>