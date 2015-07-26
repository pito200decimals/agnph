<?php
// Page handling ajax requests for tag names.

// Assumes a constant named TABLE defines the tag table.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label).

include_once("../../includes/util/core.php");
include_once("../../includes/util/sql.php");

header('Content-type: application/json; charset=utf-8');

if (!isset($_GET['search'])) {
    echo json_encode(array());
    return;
}

$search_term = mb_strtolower($_GET['search']);
$terms = explode(" ", $search_term);
$sql_clauses = array();
foreach ($terms as $term) {
    $clause = "";
    if (startsWith($term, "type:")) {
        $type_term = mb_substr($term, 5);
        $type_char = "";
        foreach ($TAG_TYPE_MAP as $char => $name) {
            if ($type_term == strtolower($name)) {
                $type_char = $char;
            }
        }
        if (strlen($type_char) > 0) {
            $clause = "Type='$type_char'";
        }
    }
    if (strlen($clause) == 0) {
        // Normal search.
        $escaped_name = sql_escape($term);
        $clause = "UPPER(Name) LIKE UPPER('%$escaped_name%')";
    }
    $sql_clauses[] = $clause;
}
if (sizeof($terms) > 0) {
    $clauses = implode(" AND ", $sql_clauses);
} else {
    $clauses = "TRUE";
}
if (!sql_query_into($result, "SELECT * FROM ".TABLE." WHERE $clauses ORDER BY Name LIMIT 50;", 0)) {
    AJAXErr();
}
$elems = array();
while ($row = $result->fetch_assoc()) {
    $elem = array(
        "name" => $row['Name'],
        "id" => $row['TagId'],
        "class" => strtolower($row['Type'])."typetag",
        "type" => $TAG_TYPE_MAP[$row['Type']],
        "editLock" => $row['EditLocked'],
        "addLock" => $row['AddLocked']
    );
    $elems[] = $elem;
}
echo json_encode($elems);
return;

?>