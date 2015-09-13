<?php
// Main control panel for admin operations.

include_once("../../header.php");
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
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

RenderPage("admin/site/site.tpl");
return;

function HandlePost() {
    if (isset($_POST['maintenance-mode'])) {
        SetSiteSetting(MAINTENANCE_MODE_KEY, "true");
    } else {
        SetSiteSetting(MAINTENANCE_MODE_KEY, "false");
    }
    
}
?>