<?php
// Page handling ajax requests for tag names.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    echo json_encode(array());
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminGallery']) {
    echo json_encode(array());
    return;
}

define("TABLE", GALLERY_TAG_TABLE);
define("ALIAS_TABLE", GALLERY_TAG_ALIAS_TABLE);
define("IMPLICATION_TABLE", GALLERY_TAG_IMPLICATION_TABLE);
$TAG_TYPE_MAP = $GALLERY_TAG_TYPES;
include_once(SITE_ROOT."admin/tags/fetch_tag_ajax.php");
return;

?>