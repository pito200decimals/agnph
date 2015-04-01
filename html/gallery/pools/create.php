<?php
// Page for receiving POSTS for creating a pool.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserCreateOrDeletePools($user)) {
    header('HTTP/1.1 403 Permission Denied');
    die();
}
if (!isset($_POST['name'])) {
    header('HTTP/1.1 403 Permission Denied');
    die();
}

$name = $_POST['name'];
$name = substr($name, 0, MAX_POOL_NAME_LENGTH);
if (strlen($name) < MIN_POOL_PREFIX_LENGTH) {
    header('HTTP/1.1 403 Permission Denied');
    die();
}
$escaped_name = sql_escape($name);
// If there's a duplicate name, go to that page.
sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name)=UPPER('$escaped_name');", 0) or RenderErrorPage("An error occurred, please try again later.");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    header("Location: /gallery/post/?search=pool%3A".$row['PoolId']);
    return;
}

$user_id = $user['UserId'];
if (!sql_query("INSERT INTO ".GALLERY_POOLS_TABLE." (Name, CreatorUserId) VALUES ('$escaped_name', $user_id);")) RenderErrorPage("An error occurred, please try again later.");
header("Location: ".$_SERVER['HTTP_REFERER']);
return;

?>