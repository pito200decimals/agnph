<?php
// Page handling ajax requests for tag names.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    AJAXErr();
}
ComputePageAccess($user);
if (!$vars['canAdminFics']) {
    AJAXErr();
}

define("TABLE", FICS_TAG_TABLE);
define("ALIAS_TABLE", FICS_TAG_ALIAS_TABLE);
define("IMPLICATION_TABLE", FICS_TAG_IMPLICATION_TABLE);
$TAG_TYPE_MAP = $FICS_TAG_TYPES;
include_once(SITE_ROOT."admin/tags/fetch_tag_ajax.php");
return;

?>