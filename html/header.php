<?php
// Standard header that includes all the important files. Will reside in the site root.

// Find the site root directory.
$folder_level = "";
while (!file_exists($folder_level.__FILE__)) {
    $folder_level .= "../";
    if (strlen($folder_level) > 20) {
        die("Could not find site root!");
    }
}
if(!defined("SITE_ROOT")) {
    define("SITE_ROOT", __DIR__."/".$folder_level);
}
unset($folder_level);

// Include common headers.
include_once(SITE_ROOT."include/config.php");
include_once(SITE_ROOT."include/constants.php");
include_once(SITE_ROOT."include/util/core.php");

?>