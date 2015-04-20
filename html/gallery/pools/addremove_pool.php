<?php
// Page for receiving ajax requests to add or remove a post from a pool.

include_once("../../includes/auth/auth.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

debug($_GET);

if (!isset($user) || !CanUserAddOrRemoveFromPools($user)) {
    AJAXErr();
}
if (!isset($_GET['post']) || !is_numeric($_GET['post']) || $_GET['post'] <= 0) {
    AJAXErr();
}
if (!isset($_GET['pool']) || !is_numeric($_GET['pool']) || $_GET['pool'] <= 0) {
    AJAXErr();
}
if (!isset($_POST['action']) || !($_POST['action'] == "add" || $_POST['action'] == "remove")) {
    AJAXErr();
}

$escaped_post_id = $_GET['post'];
$escaped_pool_id = $_GET['pool'];
if ($_POST['action'] == "add") {
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or AJAXErr();
    $post = $result->fetch_assoc();
    if ($post['ParentPoolId'] != -1) AJAXErr();
    sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE PoolId='$escaped_pool_id';", 1) or AJAXErr();
    $pool = $result->fetch_assoc();
    sql_query_into($result, "SELECT count(*) FROM ".GALLERY_POST_TABLE." WHERE ParentPoolId='$escaped_pool_id';", 1) or AJAXErr();
    $num_posts_in_pool = $result->fetch_assoc()['count(*)'];
    $desired_index = $num_posts_in_pool + 1;  // 1-indexed.
    // Append to end of pool.
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET ParentPoolId='$escaped_pool_id', PoolItemOrder='$desired_index' WHERE PostId='$escaped_post_id';") or AJAXErr();
} else if ($_POST['action'] == "remove") {
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or AJAXErr();
    $post = $result->fetch_assoc();
    if ($post['ParentPoolId'] != $_GET['pool']) AJAXErr();
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET ParentPoolId='-1' WHERE PostId='$escaped_post_id';") or AJAXErr();
    // Now try to update ordering, don't error on error.
    $removed_item_order = $post['PoolItemOrder'];
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPoolId='$escaped_pool_id' ORDER BY PoolItemOrder;", 0) or die();  // Successful return.
    $index = 1;  // 1-indexed
    while ($row = $result->fetch_assoc()) {
        if ($row['PoolItemOrder'] != $index) {
            sql_query("UPDATE ".GALLERY_POST_TABLE." SET PoolItemOrder=$index WHERE PostId='$escaped_post_id';");
        }
        $index++;
    }
} else {
    AJAXErr();
}

return;
?>