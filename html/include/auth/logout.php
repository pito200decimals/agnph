<?php
// General code to destroy a user's cookie.

include_once("../config.php");
include_once("../constants.php");
include_once("../util/core.php");

function LogOut() {
	global $user;
	debug("User has been logged out");
	setcookie(COOKIE_NAME, "", time() - 3600);
	unset($user);
}

LogOut();

?>