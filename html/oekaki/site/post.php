<?php
// AJAX-submits an image post.
// URL: /oekaki/post/ => post.php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user)) AJAXErr();

// Get POST params (json format from angularjs).
$params = json_decode(file_get_contents('php://input'),true);

if (!isset($params['title']) ||
    !isset($params['text']) ||
    !isset($params['data']) ||
    !isset($params['width']) ||
    !isset($params['height']) ||
    !isset($params['adult']) ||
    !isset($params['duration'])) AJAXErr();

$title = SanitizeHTMLTags($params['title'], NO_HTML_TAGS);

$text = SanitizeHTMLTags($params['text'], DEFAULT_ALLOWED_TAGS);

$data = $params['data'];
$expected_base64_prefix = "data:image/png;base64,";
if (!startsWith($data, $expected_base64_prefix)) AJAXErr();
$data = substr($data, strlen($expected_base64_prefix));
$img = new SimpleImage();
$img->loadFromBase64($data);

$width = $params['width'];
$height = $params['height'];
if (!is_numeric($width) || !is_numeric($height)) AJAXErr();
$width = (int)$width;
$height = (int)$height;
if ($width != $img->getWidth()) AJAXErr();
if ($height != $img->getHeight()) AJAXErr();

$adult = $params['adult'];
if (!is_numeric($adult)) AJAXErr();
$adult = (int)$adult;
if ($adult != 0 && $adult != 1) AJAXErr();

$duration = $params['duration'];
if (!is_numeric($duration)) AJAXErr();
$duration = (int)$duration;
if ($duration <= 0) AJAXErr();

$uid = $user['UserId'];
$timestamp = time();
$escaped_title = sql_escape($title);
$escaped_text = sql_escape($text);
$extension = OEKAKI_THUMB_FILE_EXTENSION;
if (sql_query("INSERT INTO ".OEKAKI_POST_TABLE."
    (UserId, ParentPostId, Timestamp, Title, Text, Width, Height, Extension, Adult, Duration)
    VALUES
    ($uid, -1, $timestamp, '$escaped_title', '$escaped_text', $width, $height, '$extension', $adult, $duration)")) {
    $pid = sql_last_id();
    // Save image file.
    $img_path = "oekaki/site/data/$pid.".OEKAKI_THUMB_FILE_EXTENSION;
    $img->save(SITE_ROOT.$img_path);
    // Log action.
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> published new post #$pid", "O");
    // Return success.
    echo json_encode(array("status" => "success"));
    exit();
} else {
    AJAXErr();
}

?>