<?php
// General code to set up a user's login cookie. Should be included INSTEAD of auth.php.

include_once(__DIR__."/../config.php");
include_once(__DIR__."/../constants.php");
include_once(__DIR__."/../util/core.php");
include_once(__DIR__."/auth.php");

// Returns true and sets global $user if login successful. Also sets cookies.
// Returns false and unsets global $user if login unsuccessful. Also unsets cookies.
// Either way, cookies are set/unset in some way.
function Login($username, $password) {
	// TODO: Look up only uid, email and password from user db (enough to compute the cookies).
	$uid = "1";
	$email = "";
	$encryptedPassword = "";
	
	if ($encryptedPassword !== md5($password)) {
		// Did not provide correct password.
		//debug("Incorrect credentials provided");
		//UnsetCookies();
		//return false;
	}
	$salt = md5($email.$encryptedPassword);
	if (AuthenticateUser($uid, $salt)) {
		setcookie(UID_COOKIE, $uid, time() + COOKIE_DURATION);
		setcookie(SALT_COOKIE, $salt, time() + COOKIE_DURATION);
		return true;
	} else {
		// Cookies are unset by AuthenticateUser().
		return false;
	}
}

if (!isset($user)) {
	Login($_POST['username'], $_POST['password']);
}

?>