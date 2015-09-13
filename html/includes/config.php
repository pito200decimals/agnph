<?php
// General site-authentication code, used to define access to server db's and such.

// Debug settings.
ini_set("display_errors", "On");
ini_set("display_startup_errors", "On");
ini_set("error_reporting", E_ALL);

// MySQL DB settings.
$dbhost = "localhost";
$dbuser = "";  // Don't show this in github :)
$dbpass = "";  // Don't show this in github :)
$dbname = "agnph";
define("OEKAKI_CRYPT_SALT", "");  // Don't show this in github :)

?>