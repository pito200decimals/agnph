<?php
// Page for displaying a list of fics tags.
// URL: /fics/tags/?page={page}

include_once("../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");

define("TABLE", FICS_TAG_TABLE);
define("TAGS_PER_PAGE", FICS_LIST_ITEMS_PER_PAGE);
$TAG_TYPE_MAP = $FICS_TAG_TYPES;

include_once(SITE_ROOT."includes/tagging/tags.php");

RenderPage("fics/tagindex.tpl");
return;
?>