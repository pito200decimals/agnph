<?php
// General user-authentication code. Will be included and run once at the beginning of each public-facing page.

include_once("../config.php");
include_once("../constants.php");
include_once("../util/core.php");

if (UserLoggedIn()) {
	die("ERROR: Already defined \$user: $user");
}
if (!isset($_COOKIE[COOKIE_NAME])) {
	// Normal guest user. Don't define $user.
	return;
}

function Login($cookie) {
	global $user;
	// TODO: Get $uid
	// TODO: Lookup password and login info from db.
	$user = array('uid' => 0);  // TODO: Initialize other parameters from db data.
	// TODO: Set up cookie, db entry.
}

Login($_COOKIE[COOKIE_NAME]);

?>