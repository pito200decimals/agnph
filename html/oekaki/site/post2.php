<?php
// AJAX-submits an image post (current Oekaki).
// URL: /oekaki/post2/ => post2.php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."oekaki/site/includes/functions.php");

ini_set('memory_limit', '600M');

// Skip if in maintenance mode.
if (!CanPerformSitePost()) AJAXErr();

if (!isset($user)) AJAXErr();

if (!CanUserCreatePost($user)) AJAXErr();

if (!isset($_POST['title']) ||
    !isset($_POST['width']) ||
    !isset($_POST['height']) ||
    !isset($_POST['duration']) ||
    !isset($_POST['post'])) {
    AJAXErr();
}
if (!isset($_FILES['file']) ||
    !isset($_FILES['image'])) {
    AJAXErr();
}
if (!is_numeric($_POST['width']) ||
    !is_numeric($_POST['height']) ||
    !is_numeric($_POST['duration'])) {
    AJAXErr();
}
$title = SanitizeHTMLTags($_POST['title'], NO_HTML_TAGS);
$width = (int) $_POST['width'];
$height = (int) $_POST['height'];
$duration = (int) $_POST['duration'];
$post = SanitizeHTMLTags($_POST['post'], DEFAULT_ALLOWED_TAGS);
if (strlen($title) <= 0) AJAXErr();
if ($width < 0) AJAXErr();
if ($height < 0) AJAXErr();
if ($duration < 0) AJAXErr();
if (strlen($post) <= 0) AJAXErr();

$uid = $user['UserId'];
$timestamp = time();
$escaped_title = sql_escape($title);
$escaped_text = sql_escape($post);
$extension = OEKAKI_THUMB_FILE_EXTENSION;
$extension = "png";
$adult = 0;
$has_animation = 1;

if (sql_query("INSERT INTO ".OEKAKI_POST_TABLE."
    (UserId, ParentPostId, Timestamp, Title, Text, Width, Height, Extension, Adult, Duration, HasAnimation, Version)
    VALUES
    ($uid, -1, $timestamp, '$escaped_title', '$escaped_text', $width, $height, '$extension', $adult, $duration, $has_animation, 2)")) {
    $pid = sql_last_id();
    // Save uploaded files.
    $png_file_path = SITE_ROOT."oekaki/site/data/${pid}.".OEKAKI_THUMB_FILE_EXTENSION;
    $oekaki_file_path = SITE_ROOT."oekaki/site/data/${pid}.oekaki";
    if (move_uploaded_file($_FILES['image']['tmp_name'], $png_file_path) !== TRUE) {
        sql_query("DELETE FROM ".OEKAKI_POST_TABLE." WHERE PostId=$pid");
        AJAXErr();
    }
    if (move_uploaded_file($_FILES['file']['tmp_name'], $oekaki_file_path) !== TRUE) {
        unlink($png_file_path);
        sql_query("DELETE FROM ".OEKAKI_POST_TABLE." WHERE PostId=$pid");
        AJAXErr();
    }
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> published new post #$pid", "O");
    echo $pid;
    exit();
} else {
    AJAXErr();
}

?>