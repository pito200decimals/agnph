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
if (!$vars['canAdminFics']) {
    DoRedirect();
}

define("TABLE", FICS_TAG_TABLE);
include_once(SITE_ROOT."admin/tags/tag_management.php");

$vars['section'] = "fics";

$vars['admin_section'] = "fics";
RenderPage("admin/fics/tag_management.tpl");
return;

?>