<?php
// AJAX for auto-completion of pool names.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($_GET['query'])) AJAXErr();
$search = $_GET['query'];
$search = RawToSanitizedPoolName($search);
$elems = array();
if ($search === FALSE) {
    // Return empty list.
} else {
    $escaped_search = sql_escape($search);
    if (sql_query_into($result, "SELECT PoolId, Name FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name) LIKE UPPER('$escaped_search%') LIMIT 3;", 1)) {
        $elems = array();
        while ($row = $result->fetch_assoc()) {
            $elem = array(
                "value" => SanitizedToRawPoolName($row['Name']),
                "data" => array(
                    "id" => $row['PoolId'],
                )
            );
            $elems[] = $elem;
        }
    }
}
$response = array(
    "query" => "Unit",
    "suggestions" => $elems,
    "term" => $search);
echo json_encode($response);
return;

?>