<?php
// Page for receiving ajax requests to fetch list of pools matching a string.

// Don't include headers, we only care about sql since this does not modify stuff.
define("DEBUG", false);
include_once("../../includes/config.php");
include_once("../../includes/constants.php");
include_once("../../includes/util/core.php");
include_once("../../includes/util/sql.php");

header('Content-type: application/json; charset=utf-8');

if (!isset($_GET['prefix']) || strlen($_GET['prefix']) < MIN_POOL_PREFIX_LENGTH) {
    echo json_encode(array());
    die();
}

$escaped_prefix = sql_escape($_GET['prefix']);
if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name) LIKE UPPER('$escaped_prefix%') ORDER BY Name LIMIT 5;", 0)) {
    header('HTTP/1.1 403 Permission Denied');
    die();
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