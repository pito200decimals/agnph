<?php
// AJAX for auto-completion of tag names.
// Assumes SITE_ROOT and TABLE_NAME is defined.

include_once(SITE_ROOT."ajax_header.php");

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
if (sql_query_into($result,
    "SELECT TagId, Name, Type FROM ".TABLE_NAME." ".
    "WHERE HideTag=0 AND AddLocked=0 AND UPPER(Name) LIKE UPPER('$escaped_search%') AND TagCount>".MIN_TAG_COUNT_FOR_AUTOCOMPLETE." ".
    "LIMIT ".NUM_AUTOCOMPLETE_RESULTS." ORDER BY Name;", 1)) {
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