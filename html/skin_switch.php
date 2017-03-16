<?php
// Page for receiving skin-switch POSTS and redirecting the user back to the page they came from.

include_once("header.php");
include_once(SITE_ROOT."includes/constants.php");

if (!isset($_POST['skin'])) {
    InvalidURL();
}

if (contains($_POST['skin'], ".") || contains($_POST['skin'], "/") || contains($_POST['skin'], "\\") || !in_array($_POST['skin'], $vars['availableSkins'])) {
    RenderErrorPage("Invalid site skin");
}
if (isset($user)) {
    if (!CanPerformSitePost()) MaintenanceError();
    if ($_POST['skin'] != $user['Skin']) {
        $escaped_skin = sql_escape(GetSanitizedTextTruncated($_POST['skin'], NO_HTML_TAGS, MAX_SKIN_STRING_LENGTH));
        sql_query("UPDATE ".USER_TABLE." SET Skin='$escaped_skin' WHERE UserId=".$user['UserId'].";");
        // Set session and cookie.
        setcookie("Skin", $_POST['skin'], time() + COOKIE_DURATION, "/");
        $_SESSION['Skin'] = $_POST['skin'];
        PostSessionBanner("Skin changed", "green");
    }
} else {
    if ($_POST['skin'] != $_SESSION['Skin']) {
        // Set session and cookie.
        setcookie("Skin", $_POST['skin'], time() + COOKIE_DURATION, "/");
        $_SESSION['Skin'] = $_POST['skin'];
        PostSessionBanner("Skin changed", "green");
    }
}

Redirect($_SERVER['HTTP_REFERER']);
?>