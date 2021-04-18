<?php
// AJAX-queries the list of current livestreams.
// URL: /oekaki/streams/ => streams.php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."oekaki/site/includes.functions.php");

$streams = GetActiveLivestreams();
$result = array();
foreach ($streams as $stream) {
    $result[] = array(
        'name' => $stream['DisplayName'],
        'url' => "https://agn.ph/oekaki/draw/#live".$stream['UserId'],
        'avatar' => $stream['avatarURL'],
    );
}

echo json_encode(array('streams' => $result));
exit();
?>