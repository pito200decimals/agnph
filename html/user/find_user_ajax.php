<?php
// Fetches a user based on DisplayName, from an ajax request.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");

header('Content-type: application/json; charset=utf-8');

if (!isset($_GET['query'])){
    echo json_encode(array());
    return;
}

$escaped_prefix = sql_escape($_GET['query']);
$where = "UPPER(DisplayName) LIKE UPPER('%$escaped_prefix%') AND Usermode=1 AND RegisterIP<>''";
if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE $where ORDER BY DisplayName LIMIT 5;", 0)) {
    AJAXErr();
}
$elems = array();
while ($row = $result->fetch_assoc()) {
    $elem = array(
        "value" => $row['DisplayName'],
        "data" => $row['UserId']
    );
    $elems[] = $elem;
}
$response = array(
    "query" => "Unit",
    "suggestions" => $elems);
echo json_encode($response);
return;
?>