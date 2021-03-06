<?php
// Page for receiving ajax requests to set the order of posts in a pool.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserChangePoolOrdering($user)) {
    AJAXErr();
}
if (!isset($_GET['pid']) || !is_numeric($_GET['pid']) || $_GET['pid'] <= 0 || !isset($_POST['values'])) {
    AJAXErr();
}
if (!CanPerformSitePost()) AJAXErr();

foreach ($_POST['values'] as $elem) {
    $post_id = sql_escape($elem['postid']);
    $order = sql_escape($elem['newindex']);
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET PoolItemOrder='$order' WHERE PostId='$post_id';")) {
        AJAXErr();
    }
}

echo json_encode(array());
return;

?>