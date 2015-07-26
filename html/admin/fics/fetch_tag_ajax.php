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
if (!$vars['canAdminFics']) {
    echo json_encode(array());
    return;
}

define("TABLE", FICS_TAG_TABLE);
$TAG_TYPE_MAP = $FICS_TAG_TYPES;
include_once(SITE_ROOT."admin/tags/fetch_tag_ajax.php");
return;

?>