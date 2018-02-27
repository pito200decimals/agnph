<?php
// Gallery admin page that shows edit history for all posts, with some filter capability (e.g. by user, post).
// URL: /admin/gallery/edit-history/
// URL: /admin/gallery/edit_history.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminGallery']) {
    DoRedirect();
}

$sql_clause = "TRUE";
$search = "";
if (isset($_GET['search'])) $search = $_GET['search'];
$escaped_search = sql_escape($search);
$search_terms = array_filter(explode(" ", $search), mb_strlen);
if (sizeof($search_terms) > 0) {
    $sql_clauses = array();
    foreach ($search_terms as $term) {
        $clause = MakeSearchClause($term);
        $sql_clauses[] = "($clause)";
    }
    // Adjust search clauses for username/post-id.
    $sql_clause = implode(" AND ", $sql_clauses);
}

CollectItems(GALLERY_POST_TAG_HISTORY_TABLE, "WHERE ($sql_clause) ORDER BY Timestamp DESC, Id DESC", $tag_history_items, GALLERY_LIST_ITEMS_PER_PAGE, $iterator, "Edit history not found");

if (sizeof($search_terms) > 0) {
    // Convert search clause into entire edit history for all associated posts.
    // Only need to special-case if a search term is given.
    $ids = array();  // Ids to keep in final result.
    $pids = array();
    foreach ($tag_history_items as $row) {
        $ids[] = $row['Id'];
        $pids[] = $row['PostId'];
    }
    $pids = array_unique($pids);
    sort($pids);
    if (sizeof($pids) > 0) {
        $sql_clause = "T.PostId IN (".implode(",", $pids).")";
    } else {
        $sql_clause = "FALSE";
    }
    $tag_history_items = array();
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_HISTORY_TABLE." T WHERE ($sql_clause) ORDER BY Timestamp DESC, Id DESC;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $tag_history_items[] = $row;
        }
    }
}

if (sizeof($tag_history_items) > 0) {
include(SITE_ROOT."gallery/includes/tag_history_include.php");
}

$vars['tagHistoryItems'] = $tag_history_items;
$vars['postIterator'] = $iterator;
$vars['search'] = $search;

$vars['admin_section'] = "gallery";
RenderPage("admin/gallery/edit_history.tpl");
return;

function MakeSearchClause($term) {
    if (startsWith($term, "-")) {
        $clause = MakeSearchClause(mb_substr($term, 1));
        return "NOT($clause)";
    }
    $escaped_term = sql_escape($term);  // Also for PostId
    $username_match = "%$escaped_term%";
    $tag_name_match = "%$escaped_term%";
    if (startsWith($term, "\"") && endsWith($term, "\"")) {
        $term = mb_substr($term, 1, mb_strlen($term) - 2);
        $escaped_term = sql_escape($term);  // Also for PostId
        $username_match = "$escaped_term";
        $tag_name_match = "$escaped_term";
    }
    // Possible searches:
    // Post Id.
    // Editor Username.
    // Tag name.
    $clauses = array();
    if (is_numeric($term)) {
        $clauses[] = "(T.PostId='$escaped_term')";
    }
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." U WHERE UPPER(U.DisplayName) LIKE UPPER('$username_match')", 1)) {
        $user_ids = array();
        $exact_users = array();
        while ($row = $result->fetch_assoc()) {
            $user_ids[] = $row['UserId'];
            if (strtoupper($row['DisplayName']) == strtoupper($term)) {
                $exact_users[] = $row['UserId'];
            }
        }
        if (sizeof($user_ids) <= 3) {
            $clauses[] = "(T.UserId IN (".implode(",", $user_ids)."))";
        } else if (sizeof($exact_users) == 1) {
            $clauses[] = "(T.UserId='".$exact_users[0]."')";
        }
    }
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_TAG_TABLE." WHERE UPPER(Name) LIKE UPPER('$tag_name_match');", 1)) {
        $tag_ids = array();
        $exact_tags = array();
        while ($row = $result->fetch_assoc()) {
            $tag_ids[] = $row['TagId'];
            if ($row['Name'] == $term) {
                $exact_tags[] = $row['TagId'];
            }
        }
        if (sizeof($tag_ids) <= 3) {
            $id_or = implode("|", array_map(function($id) { return "^$id$|^$id,|,$id$|,$id,"; }, $tag_ids));
            $clauses[] = "((T.TagsAdded IS NOT NULL AND T.TagsAdded REGEXP '$id_or') OR (T.TagsRemoved IS NOT NULL AND T.TagsRemoved REGEXP '$id_or'))";
        } else if (sizeof($exact_tags) == 1) {
            $id_or = implode("|", array_map(function($id) { return "^$id$|^$id,|,$id$|,$id,"; }, $exact_tags));
            $clauses[] = "((T.TagsAdded IS NOT NULL AND T.TagsAdded REGEXP '$id_or') OR (T.TagsRemoved IS NOT NULL AND T.TagsRemoved REGEXP '$id_or'))";
        }
    }
    $clause = implode(" OR ", $clauses);
    return $clause;
}
?>