<?php
// Page for receiving ajax requests to fetch list of pools matching a string.

// Don't include headers, we only care about sql since this does not modify stuff.
include_once("../../includes/config.php");
include_once("../../includes/constants.php");
include_once("../../includes/util/core.php");
include_once("../../includes/util/sql.php");

header('Content-type: application/json; charset=utf-8');

if (!isset($_GET['prefix']) || mb_strlen($_GET['prefix']) < MIN_POOL_PREFIX_LENGTH) {
    echo json_encode(array());
    return;
}

$escaped_prefix = sql_escape($_GET['prefix']);
if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name) LIKE UPPER('$escaped_prefix%') ORDER BY Name LIMIT 5;", 0)) {
    AJAXErr();
}
$elems = array();
while ($row = $result->fetch_assoc()) {
    $elem = array(
        "name" => $row['Name'],
        "id" => $row['PoolId']
    );
    $elems[] = $elem;
}
echo json_encode($elems);
return;

?>