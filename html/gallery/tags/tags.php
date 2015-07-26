<?php
// Page for displaying a list of gallery tags.
// URL: /gallery/tags/?page={page}

include_once("../../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");

define("TABLE", GALLERY_TAG_TABLE);
define("TAG_ITEM_TABLE", GALLERY_POST_TAG_TABLE);
define("TAGS_PER_PAGE", GALLERY_LIST_ITEMS_PER_PAGE);
$TAG_TYPE_MAP = $GALLERY_TAG_TYPES;

include_once(SITE_ROOT."includes/tagging/tags.php");

RenderPage("gallery/tags/tagindex.tpl");
return;
?>