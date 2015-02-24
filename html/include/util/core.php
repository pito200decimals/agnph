<?php
// Core utility functions for general php code.

function UserLoggedIn() {
	global $user;
	return isset($user);
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