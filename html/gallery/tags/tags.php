<?php
// Page for displaying a list of gallery tags.
// URL: /gallery/tags/?page={page}

include_once("../../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");

define("TABLE", GALLERY_TAG_TABLE);
define("TAGS_PER_PAGE", GALLERY_LIST_ITEMS_PER_PAGE);
define("ALIAS_TABLE", GALLERY_TAG_ALIAS_TABLE);
$TAG_TYPE_MAP = $GALLERY_TAG_TYPES;

include_once(SITE_ROOT."includes/tagging/tags.php");

// Return API results if specified.
if (isset($_GET['api'])) {
    $api_type = $_GET['api'];
    if ($api_type == "xml") {
        RenderPage("gallery/tags/tagindex.xml.tpl");
        return;
    }
}
RenderPage("gallery/tags/tagindex.tpl");
return;
?>