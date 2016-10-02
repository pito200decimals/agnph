<?php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."admin/includes/functions.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

if (!isset($user)) {
    AJAXErr();
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminGallery']) {
    AJAXErr();
    return;
}

UpdateAllTagCounts(GALLERY_TAG_TABLE, GALLERY_POST_TAG_TABLE, GALLERY_POST_TABLE, "PostId", /*PostFilter=*/"I.Status<>'D'", /*TagFilter=*/"TRUE");
return;
?>