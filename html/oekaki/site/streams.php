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
if (!isset($content['uids'])) AJAXErr();
if (!is_array($content['uids'])) AJAXErr();
$ids = join(",", $content['uids']);
$ids = sql_escape($ids);
sql_query("REPLACE INTO ".SITE_SETTINGS_TABLE." (Name, Value) VALUES ('".OEKAKI_LIVESTREAM_IDS_KEY."', '$ids')");
exit();

?>