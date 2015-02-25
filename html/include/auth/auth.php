<?php
// General user-authentication code. Will be included and run once at the beginning of each public-facing page.

include_once("../config.php");
include_once("../constants.php");
include_once("../util/core.php");

if (isset($user)) {
	debug_die("Already defined \$user: $user");
}

// Returns true and sets the global $user on successful authentication.
// Returns false and unsets the global $user on unsuccessful authentication.
// If false is returned, cookies are automatically unset.
function AuthenticateUser($uid, $salt) {
	global $user;
	debug("Authenticating user with uid=$uid, salt=$salt");
	// TODO: Look up all user info from db and initialize the $user object.
	$user = array('uid' => $uid, 'email' => "", 'password' => "");
	
	$targetSalt = md5($user['email'].$user['password']);
	if ($targetSalt !== $salt) {
		// Cookie did not match user credentials, do not log in.
		debug("User did not pass authentication");
		UnsetCookies();
		unset($user);
		return false;
	}
	debug("User has been authenticated!");
	debug($user);
	return true;
}

// Try to authenticate if cookies exist.
if (CookiesExist()) {
	AuthenticateUser($_COOKIE[UID_COOKIE], $_COOKIE[SALT_COOKIE]);
} else {
	debug("User is a guest!");
	// Normal guest user. Don't define $user.
	unset($user);
	return;
}

?>