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
if (mb_strlen($search) > 0) {
    // Adjust search clauses for username/post-id.
    $escaped_search = sql_escape($search);
    $sql_clause = "(PostId='$escaped_search') OR (EXISTS(SELECT 1 FROM ".USER_TABLE." U WHERE U.UserId=T.UserId AND UPPER(U.DisplayName) LIKE UPPER('%$escaped_search%')))";
}

CollectItems(GALLERY_POST_TAG_HISTORY_TABLE, "WHERE $sql_clause ORDER BY Timestamp DESC", $tag_history_items, GALLERY_LIST_ITEMS_PER_PAGE, $iterator, "Edit history not found");

if (sizeof($tag_history_items) > 0) {
include(SITE_ROOT."gallery/includes/tag_history_include.php");
}

$vars['tagHistoryItems'] = $tag_history_items;
$vars['postIterator'] = $iterator;
$vars['search'] = $search;

$vars['admin_section'] = "gallery";
RenderPage("admin/gallery/edit_history.tpl");
return;
?>