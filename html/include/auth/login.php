<?php
// General code to set up a user's login cookie.

include_once("../config.php");
include_once("../constants.php");
include_once("../util/core.php");

// Authenticates the user if already logged in.
include_once("auth.php");

if (UserLoggedIn()) {
	debug_die("Can't include login.php when the user is already logged in!");
}
if (isset($_COOKIE[COOKIE_NAME])) {
	// User cookie already set.
	debug_die("Unexpected user cookie already set: ".$_COOKIE[COOKIE_NAME]);
}

function Login($username, $password) {
	// TODO: Actually generate a cookie.
	$cookie = "COOKIE";
	setcookie(COOKIE_NAME, $cookie, time() + COOKIE_DURATION);
	Authenticate($cookie);
}

// TODO: Obfuscate the field names.
Login($_POST['username'], $_POST['password']);

?>