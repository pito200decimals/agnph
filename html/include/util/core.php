<?php
// Core utility functions for general php code.

function UserLoggedIn() {
	global $user;
	return isset($user);
}

?>