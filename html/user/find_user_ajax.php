<?php
// Fetches a user based on DisplayName, from an ajax request.
// URL: /user/search/
//
// Expects one of the following:
//   - The field 'query' to contain the name string.
//   - The field 'id' to contain the user id.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");
include_once("../includes/util/user.php");

header('Content-type: application/json; charset=utf-8');

if (isset($_GET['query'])) {
    // Used in various auto-completes (e.g. sending mail).
    $escaped_prefix = sql_escape($_GET['query']);
    $where = "UPPER(DisplayName) LIKE UPPER('%$escaped_prefix%') AND Usermode=1 AND ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION."";
    if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE $where ORDER BY DisplayName LIMIT 5;", 0)) {
        AJAXErr();
        return;
    }
    $elems = array();
    while ($row = $result->fetch_assoc()) {
        $elems[] = array(
            "value" => $row['DisplayName'],
            "data" => $row['UserId']
        );
    }
    $response = array(
        "query" => "Unit",
        "suggestions" => $elems);
    echo json_encode($response);
} else if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $where = "UserId=$user_id";
    if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE $where ORDER BY UserId LIMIT 1;", 0)) {
        AJAXErr();
        return;
    }
    $elems = array();
    while ($row = $result->fetch_assoc()) {
        $elems[] = array(
            "name" => $row['DisplayName'],
            "avatar" => GetAvatarURL($row),
        );
    }
    if (sizeof($elems) != 1) {
        AJAXErr();
        return;
    }
    $response = $elems[0];
    echo json_encode($response);
} else {
    echo json_encode(array());
}
?>