<?php
// General user-authentication code. Will be included and run once at the beginning of each public-facing page.

include_once("../config.php");
include_once("../constants.php");
include_once("../util/core.php");

if (UserLoggedIn()) {
	debug_die("Already defined \$user: $user");
}
if (!isset($_COOKIE[COOKIE_NAME])) {
	debug("User is a guest!");
	// Normal guest user. Don't define $user.
	unset($user);
	return;
}

function Authenticate($cookie) {
	debug("Authenticating user with cookie=$cookie");
	global $user;
	// TODO: Get $uid, $md5 from cookie
	$uid = 0;
	$md5 = md5("");
	// TODO: Lookup password and login info from db.
	$db_md5 = md5("");
	if ($md5 !== $db_md5) {
		// Cookie did not match user credentials, do not log in.
		debug("User did not pass authentication");
		unset($user);
		return;
	}
	// TODO: Initialize $user variable.
	$user = array('uid' => $uid);  // TODO: Initialize other parameters from db data.
	debug("User has been authenticated!");
}

Authenticate($_COOKIE[COOKIE_NAME]);

?>