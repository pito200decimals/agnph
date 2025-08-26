<?php
// General site-authentication code, used to define access to server db's and such.

if(!defined("USE_DEBUG_PHP_SETTINGS")) {
    define("USE_DEBUG_PHP_SETTINGS", false);
}
if (USE_DEBUG_PHP_SETTINGS) {
    // Debug settings.
    ini_set("display_errors", "On");
    ini_set("display_startup_errors", "On");
    ini_set("error_reporting", E_ALL);
    mysqli_report(MYSQLI_REPORT_ERROR); // Log SQL errors, return false
} else {
    // Non-debug settings
    ini_set("display_errors", "Off");
    ini_set("display_startup_errors", "Off");
    ini_set("error_reporting", E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    set_time_limit(120);
    ini_set('memory_limit', '600M');
    mysqli_report(MYSQLI_REPORT_OFF); // Suppress SQL errors, return false instead
}


// MySQL DB settings.
$dbhost = "localhost";
$dbuser = "";  // Don't show this in github :)
$dbpass = "";  // Don't show this in github :)
$dbname = "agnph";
define("GALLERY_CRYPT_SALT", "");  // Don't show this in github :)
define("OEKAKI_CRYPT_SALT", "");  // Don't show this in github :)
define("IRC_MIRROR_POST_SECRET_KEY", "");  // Don't show this in github :)

?>
