<?php
// Page for receiving POSTS for deleting a pool.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserCreateOrDeletePools($user)) {
    header('HTTP/1.1 403 Permission Denied');
    die();
}
if (!isset($_POST['pool'])) {
    header('HTTP/1.1 403 Permission Denied');
    die();
}

$pool_id = $_POST['pool'];
$escaped_pool_id = $pool_id;
// Ensure pool exists.
sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE PoolId='$escaped_pool_id';", 1) or RenderErrorPage("An error occurred, please try again later.");
sql_query("DELETE FROM ".GALLERY_POOLS_TABLE." WHERE PoolId='$escaped_pool_id';") or RenderErrorPage("An error occurred, please try again later.");
header("Location: ".$_SERVER['HTTP_REFERER']);
return;

?>