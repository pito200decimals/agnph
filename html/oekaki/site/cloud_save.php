<?php
// AJAX-queries for the new oekaki cloud data slot.
// URL: /oekaki/cloud/save/ => cloud_save.php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

// Skip if in maintenance mode.
if (!CanPerformSitePost()) AJAXErr();

if (!isset($user)) AJAXErr();

if (!isset($_POST['id']) ||
    !isset($_POST['key']) ||
    !isset($_POST['data'])) {
    AJAXErr();
}

if ($user['UserId'] != $_POST['id']) {
    AJAXErr();
}

$uid = (int)$user['UserId'];

if ($_POST['key'] == 'cloud_slot') {
    mkdir(SITE_ROOT."user/data/oekaki/cloud/");
    $cloud_data_path = SITE_ROOT."user/data/oekaki/cloud/$uid.cloud_slot.oekaki";
    $fp = fopen($cloud_data_path, 'w');
    fwrite($fp, $_POST['data']);
    fclose($fp);
    echo json_encode(array('success' => TRUE));
    exit();
}
// If unknown key, return an error.
AJAXErr();
?>