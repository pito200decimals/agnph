<?php
// Page handling ajax requests for tag names.

// Assumes a constant named TABLE defines the tag table, ALIAS_TABLE defines the alias table, and IMPLICATION_TABLE defines the implication table.
// Also that $TAG_TYPE_MAP is initialized to (from letter to label).

// Assumes that if a user can access this admin page, they can also create tags.

include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

if (!isset($_GET['search']) || !isset($_GET['filter'])) {
    AJAXErr();
}

$search_term = mb_strtolower($_GET['search'], "UTF-8");
$filter = $_GET['filter'];
$sql_clauses = array();
// Add filter clauses.
if ($filter == "alias") {
    $sql_clauses[] = "EXISTS(SELECT 1 FROM ".ALIAS_TABLE." Q WHERE T.TagId=Q.TagId OR T.TagId=Q.AliasTagId)";
} else if ($filter == "implication") {
    $sql_clauses[] = "EXISTS(SELECT 1 FROM ".IMPLICATION_TABLE." I WHERE T.TagId=I.TagId OR T.TagId=I.ImpliedTagId)";
} else if ($filter == "create" && !contains($search_term, ":")) {
    // Create new tag before performing search.
    // Although this is a GET that modifies stuff... it can only be accessed from admin page, and per-section permission checks are applied before this page is included.
    if (!isset($user)) {
        AJAXErr();
    }
    $search_term = SanitizeTagName($search_term);
    GetTagsByName(TABLE, array($search_term), true /* create_new */, $user['UserId']);
}
$offset = 0;
if (strlen($search_term) > 0) {
    $terms = explode(" ", $search_term);
    foreach ($terms as $term) {
        if ($term == "type:locked") {
            $sql_clauses[] = "T.EditLocked=1";
            continue;
        } else if ($term == "type:unlocked") {
            $sql_clauses[] = "T.EditLocked=0";
            continue;
        }
        if ($term == "add:locked") {
            $sql_clauses[] = "T.AddLocked=1";
            continue;
        } else if ($term == "add:unlocked") {
            $sql_clauses[] = "T.AddLocked=0";
            continue;
        }
        if (startsWith($term, "offset:")) {
            $offset_term = mb_substr($term, 7);
            if (is_numeric($offset_term)) $offset = (int)$offset_term;
            continue;
        }
        if (startsWith($term, "type:")) {
            $type_term = mb_substr($term, 5);
            $type_char = "";
            foreach ($TAG_TYPE_MAP as $char => $name) {
                if ($type_term == strtolower($name)) {
                    $type_char = $char;
                }
            }
            if (strlen($type_char) > 0) {
                $sql_clauses[] = "T.Type='$type_char'";
                continue;
            }
        }
        if ($term == "type:hidden") {
            $sql_clauses[] = "T.HideTag=1";
            continue;
        } else if ($term == "type:not_hidden") {
            $sql_clauses[] = "T.HideTag=0";
            continue;
        }
        // Search for term.
        if (startsWith($term, "\"") && endsWith($term, "\"")) {
            // Exact search.
            $escaped_name = sql_escape(mb_substr($term, 1, mb_strlen($term) - 2));
            $sql_clauses[] = "UPPER(T.Name) LIKE UPPER('$escaped_name')";
        } else {
            // Substring search.
            $escaped_name = sql_escape($term);
            $sql_clauses[] = "UPPER(T.Name) LIKE UPPER('%$escaped_name%')";
        }
    }
}
if (sizeof($sql_clauses) > 0) {
    $clauses = implode(" AND ", $sql_clauses);
} else {
    $clauses = "TRUE";
}
$table_clauses = $clauses;
$alias_clauses = str_replace("T.", "R.", $clauses);
// Match a tag when any of the following:
// - This tag matches the query.
// - There's a rule that aliases to this tag, and the original tag matches the query.
// - There's a rule that this tag aliases to, and the resulting tag matches the query.
if (!sql_query_into($result,
        "SELECT * FROM ".TABLE." T WHERE (
            $table_clauses OR
            EXISTS(SELECT 1 FROM ".ALIAS_TABLE." A WHERE(
                A.AliasTagId=T.TagId AND
                EXISTS(SELECT 1 FROM ".TABLE." R WHERE (
                    A.TagId=R.TagId AND
                    $alias_clauses
                ))
            )) OR
            EXISTS(SELECT 1 FROM ".ALIAS_TABLE." A WHERE(
                A.TagId=T.TagId AND
                EXISTS(SELECT 1 FROM ".TABLE." R WHERE (
                    A.AliasTagId=R.TagId AND
                    $alias_clauses
                ))
            ))
        ) ORDER BY Name LIMIT 50 OFFSET $offset;", 0)) {
    AJAXErr();
}
$tag_ids = array();
$elems = array();
$elems_by_id = array();
while ($row = $result->fetch_assoc()) {
    $tag_id = $row['TagId'];
    $elems_by_id[$tag_id] = CreateElem($row);
    $elems[] = &$elems_by_id[$tag_id];
    $tag_ids[] = $tag_id;
}

// Fetch alias data for relevant tags.
$joined_ids = implode(",", $tag_ids);
if (sql_query_into($result, "SELECT * FROM ".ALIAS_TABLE." WHERE TagId IN ($joined_ids) OR AliasTagId IN ($joined_ids);", 1)) {
    $temp_tag_ids = $tag_ids;
    $alias_elems = array();
    $hidden_alias_ids = array();
    while ($row = $result->fetch_assoc()) {
        $alias_elems[] = $row;
        $temp_tag_ids[] = $row['TagId'];
        $temp_tag_ids[] = $row['AliasTagId'];
    }
    $temp_tag_ids = array_values($temp_tag_ids);
    // Fetch any referenced but missing tags.
    $joined_ids = implode(",", $temp_tag_ids);
    if (sql_query_into($result, "SELECT * FROM ".TABLE." WHERE TagId IN ($joined_ids);")) {
        while ($row = $result->fetch_assoc()) {
            $tag_id = $row['TagId'];
            if (!isset($elems_by_id[$tag_id])) {
                $elems_by_id[$tag_id] = CreateElem($row);
            }
        }
        // Assign elements.
        foreach ($alias_elems as $row) {
            $elems_by_id[$row['TagId']]['alias'] = array(
                "name" => $elems_by_id[$row['AliasTagId']]['name'],
                "class" => $elems_by_id[$row['AliasTagId']]['class'],
                "count" => $elems_by_id[$row['AliasTagId']]['count']);
            $elems_by_id[$row['AliasTagId']]['aliased_by'][] = array(
                "name" => $elems_by_id[$row['TagId']]['name'],
                "class" => $elems_by_id[$row['TagId']]['class'],
                "count" => $elems_by_id[$row['TagId']]['count']);
        }
    }
}

// Fetch implication data for relevant tags.
if (sql_query_into($result, "SELECT * FROM ".IMPLICATION_TABLE." WHERE TagId IN ($joined_ids) OR ImpliedTagId IN ($joined_ids);", 1)) {
    $temp_tag_ids = $tag_ids;
    $implication_elems = array();
    while ($row = $result->fetch_assoc()) {
        $implication_elems[] = $row;
        $temp_tag_ids[] = $row['TagId'];
        $temp_tag_ids[] = $row['ImpliedTagId'];
    }
    $temp_tag_ids = array_values($temp_tag_ids);
    // Fetch any referenced but missing tags.
    $joined_ids = implode(",", $temp_tag_ids);
    if (sql_query_into($result, "SELECT * FROM ".TABLE." WHERE TagId IN ($joined_ids);")) {
        while ($row = $result->fetch_assoc()) {
            $tag_id = $row['TagId'];
            if (!isset($elems_by_id[$tag_id])) {
                $elems_by_id[$tag_id] = CreateElem($row);
            }
        }
        // Assign elements.
        foreach ($implication_elems as $row) {
            $elems_by_id[$row['TagId']]['implies'][] = array(
                "name" => $elems_by_id[$row['ImpliedTagId']]['name'],
                "class" => $elems_by_id[$row['ImpliedTagId']]['class']);
            $elems_by_id[$row['ImpliedTagId']]['implied_by'][] = array(
                "name" => $elems_by_id[$row['TagId']]['name'],
                "class" => $elems_by_id[$row['TagId']]['class']);
        }
    }
}

echo json_encode($elems);
return;

function CreateElem($row) {
    global $TAG_TYPE_MAP;
    $tag_id = $row['TagId'];
    return array(
        "name" => $row['Name'],
        "id" => $tag_id,
        "class" => strtolower($row['Type'])."typetag",
        "type" => $TAG_TYPE_MAP[$row['Type']],
        "editLock" => $row['EditLocked'],
        "addLock" => $row['AddLocked'],
        "alias" => null,
        "hide_tag" => $row['HideTag'],
        "aliased_by" => array(),
        "implies" => array(),
        "implied_by" => array(),
        "note" => $row['Note'],
        "count" => $row['ItemCount'],
    );
}

?>