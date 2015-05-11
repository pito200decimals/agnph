<?php
// Page for displaying a list of fics tags.
// URL: /fics/tags/?page={page}

include_once("../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");

define("TABLE", FICS_TAG_TABLE);
define("TAGS_PER_PAGE", FICS_LIST_ITEMS_PER_PAGE);
$TAG_TYPE_MAP = $FICS_TAG_TYPES;
$search_clause = "";

if (isset($_GET['prefix'])) {
    $prefix = mb_strtolower($_GET['prefix']);
    $escaped_prefix = sql_escape($prefix);
    $search_clause = "WHERE LOWER(Name) LIKE '$prefix%'";
}

include_once(SITE_ROOT."includes/tagging/tags.php");

RenderPage("fics/tagindex.tpl");
return;
?>