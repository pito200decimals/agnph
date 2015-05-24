<?php
// AJAX page for saving biography info.

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."user/includes/functions.php");

if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    AJAXErr();
}

// Note: This is duplicated from profile_setup.php, but throwing AJAXErr instead of normal errors.
$profile_id = (int)$_GET['uid'];
$profile_users = array();
LoadTableData(array(USER_TABLE), "UserId", array($profile_id), $profile_users) or AJAXErr();
$profile_user = $profile_users[$profile_id];
$uid = $profile_user['UserId'];  // Get database value, not user input value.

if (!isset($user) || !CanUserEditBio($user, $profile_user)) {
    AJAXErr();
}
if (!isset($_POST['bio'])) AJAXErr();

$input = $_POST['bio'];
$sanitized_input = SanitizeHTMLTags($input, DEFAULT_ALLOWED_TAGS);

write_file(SITE_ROOT."user/data/bio/$uid.txt", $sanitized_input) or AJAXErr();

echo $sanitized_input;
?>