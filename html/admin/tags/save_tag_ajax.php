<?php
// Page handling ajax requests for saving tag edits.

// Assumes a constant named TABLE defines the tag table.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label).

include_once("../../includes/util/core.php");
include_once("../../includes/util/sql.php");

if (!isset($user)) {
    AJAXErr();
}

if (!isset($_POST['id']) ||
    //!isset($_POST['name']) ||  // Do we want to allow changing tag names? Probably not...
    !isset($_POST['type']) ||
    !isset($_POST['edit']) ||
    !isset($_POST['add'])) {
    AJAXErr();
}
$tag_id = $_POST['id'];
if (!is_numeric($tag_id)) AJAXErr();
$tag_id = (int)$tag_id;

$uid = $user['UserId'];
$now = time();
$update_clauses = array();
foreach ($TAG_TYPE_MAP as $char => $name) {
    if ($_POST['type'] == $name) {
        $update_clauses[] = "Type='$char'";
        $update_clauses[] = "ChangeTypeUserId=$uid";
        $update_clauses[] = "ChangeTypeTimestamp=$now";
        break;
    }
}
if ($_POST['edit'] == "Locked") {
    $update_clauses[] = "EditLocked=1";
} else {
    $update_clauses[] = "EditLocked=0";
}
if ($POST['add'] == "Locked") {
    $update_clauses[] = "AddLocked=1";
} else {
    $update_clauses[] = "AddLocked=0";
}

$joined = implode(",", $update_clauses);
sql_query("UPDATE ".TABLE." SET $joined WHERE TagId=$tag_id;");

?>