<?php
// AJAX for auto-completion of tag names.
// Assumes SITE_ROOT and TABLE_NAME is defined.

include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");

header('Content-type: application/json; charset=utf-8');

if (!defined("TABLE_NAME")) AJAXErr();
if (!isset($_GET['query'])) AJAXErr();
$search = $_GET['query'];
$prefix = "";
$index = mb_strpos($search, ":");
if ($index > -1) {
    $prefix = mb_substr($search, 0, $index);
    $search = mb_substr($search, $index + 1);
}
$escaped_search = sql_escape($search);
if (sql_query_into($result, "SELECT TagId, Name, Type FROM ".TABLE_NAME." WHERE AddLocked=0 AND UPPER(Name) LIKE '$escaped_search%' LIMIT 3;", 1)) {
    $elems = array();
    while ($row = $result->fetch_assoc()) {
        if ($prefix == "") $prefix = strtolower($GALLERY_TAG_TYPES[$row['Type']]);
        $elem = array(
            "value" => $row['Name'],
            "data" => array(
                "id" => $row['TagId'],
                "type" => strtolower($row['Type'])
            )
        );
        $elems[] = $elem;
    }
} else {
    $elems = array();
}
$response = array(
    "query" => "Unit",
    "suggestions" => $elems);
echo json_encode($response);
return;

?>