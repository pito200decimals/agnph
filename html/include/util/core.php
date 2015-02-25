<?php
// Core utility functions for general php code.

include_once(__DIR__."/../constants.php");

function CookiesExist() {
	return isset($_COOKIE[UID_COOKIE]) && isset($_COOKIE[SALT_COOKIE]);
}

function UnsetCookies() {
	debug("User cookies have been destroyed.");
	setcookie(UID_COOKIE, "", time() - 3600);
	setcookie(SALT_COOKIE, "", time() - 3600);
}

function debug($message) {
	print("<strong>[DEBUG]</strong>: ");
	print_r($message);
	print("\n<br />");
}

function debug_die($message) {
	print("<strong>[FATAL]</strong>: ");
	print_r($message);
	print("\n<br />");
	die();
}

?>