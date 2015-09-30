<?php
// Page for receiving skin-switch POSTS and redirecting the user back to the page they came from.

include_once("header.php");

if (!isset($_POST['skin'])) {
    InvalidURL();
}

if (contains($_POST['skin'], ".") || contains($_POST['skin'], "/") || contains($_POST['skin'], "\\") || !in_array($_POST['skin'], $vars['availableSkins'])) {
    PostErrorMessage("Invalid site skin");
}
if (isset($user)) {
    if (!CanPerformSitePost()) MaintenanceError();
    if ($_POST['skin'] != $user['Skin']) {
        $escaped_skin = sql_escape(mb_strtolower($_POST['skin']));
        sql_query("UPDATE ".USER_TABLE." SET Skin='$escaped_skin' WHERE UserId=".$user['UserId'].";");
        PostSessionBanner("Skin changed", "green");
    }
} else {
    if ($_POST['skin'] != $_SESSION['Skin']) {
        $_SESSION['Skin'] = $_POST['skin'];
        PostSessionBanner("Skin changed", "green");
    }
}

header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>