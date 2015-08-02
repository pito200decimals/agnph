<?php
// Page handling ajax requests for saving tag edits.

// Assumes a constant named TABLE defines the tag table, ALIAS_TABLE defines the alias table, and IMPLICATION_TABLE defines the implication table.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label).

// Assumes that if a user can access this admin page, they can also create/modify tags.

include_once("../../includes/util/core.php");
include_once("../../includes/util/sql.php");
include_once("../../includes/tagging/tag_functions.php");

if (!isset($user)) {
    AJAXErr();
}

if (!isset($_POST['id']) ||
    //!isset($_POST['name']) ||  // Do we want to allow changing tag names? Probably not...
    !isset($_POST['type']) ||
    !isset($_POST['edit']) ||
    !isset($_POST['add']) ||
    !isset($_POST['alias']) ||
    !isset($_POST['implied']) ||
    !isset($_POST['note'])) {
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
if ($_POST['add'] == "Locked") {
    $update_clauses[] = "AddLocked=1";
} else {
    $update_clauses[] = "AddLocked=0";
}
$update_clauses[] = "Note='".sql_escape($_POST['note'])."'";
// Update tag table.
$joined = implode(",", $update_clauses);
sql_query("UPDATE ".TABLE." SET $joined WHERE TagId=$tag_id;");

// Process alias table changes.
$alias_already_exists = sql_query_into($result, "SELECT * FROM ".ALIAS_TABLE." WHERE TagId=$tag_id;", 1);
$new_alias_exists = strlen($_POST['alias']) > 0;
if ($new_alias_exists) {
    if ($alias_already_exists) {
        $old_alias_tag_id = $result->fetch_assoc()['TagId'];
    } else {
        $old_alias_tag_id = null;
    }
    $alias_tag = GetTagsByName(TABLE, array($_POST['alias']), true /* create_new */, $uid);
    if (!($alias_tag == null || sizeof($alias_tag) == 0)) {
        $alias_tag = array_values($alias_tag)[0];
        $alias_tag_id = $alias_tag['TagId'];
        if ($old_alias_tag_id != $alias_tag_id) {
            // Ensure we don't create alias loops. This call should result in exactly one result.
            $renamed_alias_tag = GetTagsByNameWithAliasAndImplied(TABLE, ALIAS_TABLE, IMPLICATION_TABLE, array($_POST['alias']), true /* create_new */, $uid, true /* do_alias */, false /* do_implication */);
            if (!($renamed_alias_tag == null || sizeof($renamed_alias_tag) == 0)) {
                $renamed_alias_tag = array_values($renamed_alias_tag)[0];
                // Don't apply if it's the same as the current tag.
                if ($renamed_alias_tag['TagId'] != $tag_id) {
                    // Update alias entry.
                    if ($old_alias_tag_id == null) {
                        sql_query("INSERT INTO ".ALIAS_TABLE." (TagId, AliasTagId, CreatorUserId, Timestamp) VALUES ($tag_id, $alias_tag_id, $uid, $now);");
                    } else {
                        sql_query("UPDATE ".ALIAS_TABLE." SET AliasTagId=$alias_tag_id, CreatorUserId=$uid, Timestamp=$now WHERE TagId=$tag_id;");
                    }
                    // Record alias change here, so parent php file can handle it if desired.
                    $original_tag_id = $tag_id;
                    $new_alias_tag_id = $renamed_alias_tag['TagId'];
                }
            }
        }
    }
} else {
    if ($alias_already_exists) {
        // Delete old alias.
        sql_query("DELETE FROM ".ALIAS_TABLE." WHERE TagId=$tag_id;");
    }
}

// Process implication table changes.
// Delete existing implications and insert new ones.
$implied_tag_names = array_filter(explode(" ", $_POST['implied']), "strlen");
$implied_tags = GetTagsByName(TABLE, $implied_tag_names, true /* create_new */, $uid);
$implied_tag_ids = array_map(function($tag) { return $tag['TagId']; }, $implied_tags);
$existing_implied_ids = array();
if (sql_query_into($result, "SELECT * FROM ".IMPLICATION_TABLE." WHERE TagId=$tag_id;", 1)) {
    while ($row = $result->fetch_assoc()) {
        $existing_implied_ids[] = $row['ImpliedTagId'];
    }
}
$ids_to_add = array();
foreach ($implied_tags as $tag) {
    if (!in_array($tag['TagId'], $existing_implied_ids)) {
        $ids_to_add[] = $tag['TagId'];
    }
}
if (sizeof($ids_to_add) > 0) {
    $values = implode(",", array_map(function($id) use ($tag_id, $uid, $now) {
        return "($tag_id, $id, $uid, $now)";
    }, $ids_to_add));
    sql_query("INSERT INTO ".IMPLICATION_TABLE." (TagId, ImpliedTagId, CreatorUserId, Timestamp) VALUES $values;");
}
if (sizeof($implied_tag_ids) > 0) {
    $joined_ids = implode(",", $implied_tag_ids);
    sql_query("DELETE FROM ".IMPLICATION_TABLE." WHERE TagId=$tag_id AND ImpliedTagId NOT IN ($joined_ids);");
} else {
    sql_query("DELETE FROM ".IMPLICATION_TABLE." WHERE TagId=$tag_id;");
}

?>