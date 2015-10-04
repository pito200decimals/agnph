<?php
// Page for receiving POSTS for deleting a pool.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserCreateOrDeletePools($user)) {
    RenderErrorPage("Not authorized to delete image pools");
}
if (!isset($_POST['pool'])) InvalidURL();
if (!CanPerformSitePost()) MaintenanceError();

$pool_id = $_POST['pool'];
$escaped_pool_id = $pool_id;
// Ensure pool exists.
sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE PoolId='$escaped_pool_id';", 1) or RenderErrorPage("An error occurred, please try again later.");
$pool = $result->fetch_assoc();
sql_query("DELETE FROM ".GALLERY_POOLS_TABLE." WHERE PoolId='$escaped_pool_id';") or RenderErrorPage("An error occurred, please try again later.");
$uid = $user['UserId'];
$username = $user['DisplayName'];
$poolName = $pool['Name'];
LogAction("<strong><a href='/user/$uid/'>$username</a></strong> deleted pool <strong>$poolName</strong>", "G");
PostSessionBanner("Pool deleted", "red");
// Go back to requesting page.
Redirect($_SERVER['HTTP_REFERER']);

?>