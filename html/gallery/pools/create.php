<?php
// Page for receiving POSTS for creating a pool.

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user) || !CanUserCreatePool($user)) {
    RenderErrorPage("Not authroized to create image pools");
}
if (!isset($_POST['search'])) InvalidURL();
if (!CanPerformSitePost()) MaintenanceError();

$name = $_POST['search'];
$name = RawToSanitizedPoolName($name, /*accept_only_valid_lengths=*/true);
if ($name === FALSE) {
    PostSessionBanner("Invalid pool name", "red");
    Redirect($_SERVER['HTTP_REFERER']);
}
$escaped_name = sql_escape($name);
// If there's a duplicate name, go to that page.
sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE UPPER(Name)=UPPER('$escaped_name');", 0) or RenderErrorPage("An error occurred, please try again later.");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $search_name = ToSearchNameString($row['Name']);
    Redirect("/gallery/post/?search=".urlencode("pool:$search_name"));
}

$user_id = $user['UserId'];
if (!sql_query("INSERT INTO ".GALLERY_POOLS_TABLE." (Name, CreatorUserId) VALUES ('$escaped_name', $user_id);")) RenderErrorPage("An error occurred, please try again later.");
$uid = $user['UserId'];
$username = $user['DisplayName'];
$name = htmlspecialchars($name);
LogAction("<strong><a href='/user/$uid/'>$username</a></strong> created pool <strong>$name</strong>", "G");
PostSessionBanner("Pool created", "green");
Redirect($_SERVER['HTTP_REFERER']);

function ToSearchNameString($name) {
    $name = str_replace(" ", "_", $name);
    return $name;
}
?>