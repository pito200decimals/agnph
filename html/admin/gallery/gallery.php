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
if (!$vars['canAdminGallery']) {
    DoRedirect();
}

$changed = false;
// TODO: Try saving settings, if posted.
if ($changed) {
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

// Get settings from table, and populate fields.
// Assume defaults to start.
// TODO: Set defaults.
// TODO: Fetch key/values from settings table.

RenderPage("admin/gallery/gallery.tpl");
return;

?>