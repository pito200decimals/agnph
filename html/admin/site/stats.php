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
if (!$vars['canAdminSite']) {
    DoRedirect();
}
$vars['is_maintenance_mode'] = IsMaintenanceMode();
$duration = CONSIDERED_ONLINE_DURATION;
if (isset($_GET['duration'])) {
    if ($_GET['duration'] == 'day') {
        $duration = 24 * 60 * 60;
    }
}
$stats = GetCurrentPageviewStats($duration);
$stat_list = array();
foreach ($stats as $page => $count) {
    $stat_list[] = array(
        'PageUrl' => $page,
        'Count' => $count,
    );
}
$vars['stats'] = $stat_list;

$vars['admin_section'] = "site";
RenderPage("admin/site/stats.tpl");
return;
?>