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
if (!$vars['canAdminGallery']) {
    DoRedirect();
}

define("TABLE", GALLERY_TAG_TABLE);
include_once(SITE_ROOT."admin/tags/tag_management.php");

$vars['section'] = "gallery";

$vars['admin_section'] = "gallery";
RenderPage("admin/gallery/tag_management.tpl");
return;

?>