<?php
// General site-authentication code, used to define access to server db's and such.

// Debug settings.
ini_set("display_errors", "On");
ini_set("display_startup_errors", "On");
ini_set("error_reporting", E_ALL);
// Non-debug settings
/*
ini_set("display_errors", "Off");
ini_set("display_startup_errors", "Off");
ini_set("error_reporting", 0);
set_time_limit(120);
ini_set('memory_limit', '100M');
*/

// MySQL DB settings.
$dbhost = "localhost";
$dbuser = "";  // Don't show this in github :)
$dbpass = "";  // Don't show this in github :)
$dbname = "agnph";
define("GALLERY_CRYPT_SALT", "");  // Don't show this in github :)
define("OEKAKI_CRYPT_SALT", "");  // Don't show this in github :)

?>