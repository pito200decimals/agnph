<?php
// AJAX-submits an image post.
// URL: /oekaki/streams/ => streams.php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

$whitelist = array('127.0.0.1', '::1');
if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist)) AJAXErr();

$content = file_get_contents("php://input");
$content = json_decode($content, TRUE);
if ($content == NULL) {
    echo "null";
    AJAXErr();
}
if (!isset($content['sessions'])) AJAXErr();
$sessions = $content['sessions'];
if (!is_array($sessions)) AJAXErr();
$ids = array();
$new_rows = array();
foreach ($sessions as $s) {
    if (!is_array($s)) AJAXErr();
    if (!isset($s['uid'])) AJAXErr();
    if (!is_int($s['uid'])) AJAXErr();
    if (!isset($s['timestamp'])) AJAXErr();
    if (!is_int($s['timestamp'])) AJAXErr();
    $uid = $s['uid'];
    $timestamp = $s['timestamp'];
    $ids[] = $uid;
    $new_rows[] = "($uid, $timestamp)";
}
$id_list = join(",", $ids);
$new_values_list = join(",", $new_rows);
if (count($ids) == 0) {
    sql_query("DELETE FROM ".OEKAKI_LIVESTREAM_TABLE." WHERE 1;");
} else {
    sql_query("DELETE FROM ".OEKAKI_LIVESTREAM_TABLE." WHERE NOT(UserId IN ($id_list));");
}
sql_query("REPLACE INTO ".OEKAKI_LIVESTREAM_TABLE." (UserId, Timestamp) VALUES $new_values_list;");
exit();

?>