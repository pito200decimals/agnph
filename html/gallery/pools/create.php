<?php
// Page for receiving POSTS for creating a pool.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserCreateOrDeletePools($user)) {
    RenderErrorPage("Not authroized to create image pools");
}
if (!isset($_POST['search'])) InvalidURL();
if (!CanPerformSitePost()) MaintenanceError();

$name = $_POST['search'];
// Strip out _ so that this can be searched for.
$name = str_replace("_", " ", $name);
// And strip out duplicate spaces.
$name = mb_ereg_replace("\s+", " ", $name);
$name = mb_substr($name, 0, MAX_POOL_NAME_LENGTH);
if (mb_strlen($name) < MIN_POOL_PREFIX_LENGTH) {
    PostSessionBanner("Invalid pool name", "red");
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
$escaped_name = sql_escape($name);
// If there's a duplicate name, go to that page.
sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name)=UPPER('$escaped_name');", 0) or RenderErrorPage("An error occurred, please try again later.");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $search_name = ToSearchNameString($row['Name']);
    header("Location: /gallery/post/?search=".urlencode("pool:$search_name"));
    exit();
}

$user_id = $user['UserId'];
if (!sql_query("INSERT INTO ".GALLERY_POOLS_TABLE." (Name, CreatorUserId) VALUES ('$escaped_name', $user_id);")) RenderErrorPage("An error occurred, please try again later.");
$uid = $user['UserId'];
$username = $user['DisplayName'];
$name = htmlspecialchars($name);
LogAction("<strong><a href='/user/$uid/'>$username</a></strong> created pool <strong>$name</strong>", "G");
PostSessionBanner("Pool created", "green");
header("Location: ".$_SERVER['HTTP_REFERER']);
exit();

function ToSearchNameString($name) {
    $name = str_replace(" ", "_", $name);
    return $name;
}
?>