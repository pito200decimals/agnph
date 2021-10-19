<?php
// Script to get thumbnail data (but only if user is logged in). Used to display png on the user profile page.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");

if (!isset($user)) AJAXErr();
if (!isset($_GET['slot']) || !is_numeric($_GET['slot'])) AJAXErr();

$slot = (int)$_GET['slot'];

if (!(0 <= $slot && $slot < MAX_OEKAKI_SAVE_SLOTS)) AJAXErr();

$uid = $user['UserId'];
$filepath = SITE_ROOT."user/data/oekaki/$uid/slot$slot/".OEKAKI_THUMB_FILE_NAME;

if (!file_exists($filepath)) AJAXErr();

header("Content-Type: image/".OEKAKI_THUMB_FILE_EXTENSION);
readfile($filepath);
?>