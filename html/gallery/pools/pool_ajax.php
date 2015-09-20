<?php
// AJAX for auto-completion of pool names.

define("SITE_ROOT", "../../");

include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");

header('Content-type: application/json; charset=utf-8');

if (!isset($_GET['query'])) AJAXErr();
$search = $_GET['query'];
$escaped_search = sql_escape($search);
if (sql_query_into($result, "SELECT PoolId, Name FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name) LIKE UPPER('$escaped_search%') LIMIT 3;", 1)) {
    $elems = array();
    while ($row = $result->fetch_assoc()) {
        $elem = array(
            "value" => $row['Name'],
            "data" => array(
                "id" => $row['PoolId'],
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