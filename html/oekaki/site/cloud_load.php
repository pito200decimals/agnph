<?php
// AJAX-queries for the new oekaki cloud data slot.
// URL: /oekaki/cloud/load/ => cloud_load.php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

// Skip if in maintenance mode.
if (!CanPerformSitePost()) AJAXErr();

if (!isset($user)) AJAXErr();

if (!isset($_GET['id']) ||
    !isset($_GET['key'])) {
        echo "error 1";
    AJAXErr();
}

if ($user['UserId'] != $_GET['id']) {
    echo "error 2";
    AJAXErr();
}

$uid = (int)$user['UserId'];

if ($_GET['key'] == 'cloud_slot') {
    $cloud_data_path = SITE_ROOT."user/data/oekaki/cloud/$uid.cloud_slot.oekaki";
    if (!file_exists($cloud_data_path)) {
        echo "error 3";
        AJAXErr();
    }
    $fp = fopen($cloud_data_path, 'r');
    $data = fread($fp, filesize($cloud_data_path));
    fclose($fp);
    echo json_encode(array('value' => $data));
    exit();
}
// If unknown key, return an error.
        echo "error 4";
AJAXErr();
?>