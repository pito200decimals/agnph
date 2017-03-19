<?php
// Main control panel for admin operations.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/date.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");
include_once(SITE_ROOT."includes/util/user_activity.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
// Note: All admins can view direct links to this page.
if (!(
    $vars['canAdminSite'] ||
    $vars['canAdminForums'] ||
    $vars['canAdminGallery'] ||
    $vars['canAdminFics'] ||
    $vars['canAdminOekaki'])) {
    DoRedirect();
}
$vars['is_maintenance_mode'] = IsMaintenanceMode();

$duration = CONSIDERED_ONLINE_DURATION;
if (isset($_GET['duration'])) {
    if ($_GET['duration'] == 'day') {
        $duration = 24 * 60 * 60;
    }
}
$column = "PageUrl";
if (isset($_GET['type'])) {
    if ($_GET['type'] == "page") {
        $column = "PageUrl";
        $vars['column_type'] = "Page URL";
    } elseif ($_GET['type'] == "useragent") {
        $column = "UserAgent";
        $vars['column_type'] = "User Agent";
    } else {
        RenderErrorPage("Unable to show stats");
        return;
    }
}
$stats = GetCurrentPageviewStats($column, $duration);
$vars['stats'] = $stats;

$vars['admin_section'] = "site";
RenderPage("admin/site/stats.tpl");
return;
?>