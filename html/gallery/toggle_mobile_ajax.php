<?php
session_start();

define("SITE_ROOT", "../");
include_once(SITE_ROOT."ajax_header.php");

if (!isset($_POST['disabled'])) AJAXErr();

$uid = $user['UserId'];
if ($_POST['disabled'] == "true") {
    $_SESSION['disable-gallery-mobile'] = true;
} else {
    $_SESSION['disable-gallery-mobile'] = false;
}
return;
?>