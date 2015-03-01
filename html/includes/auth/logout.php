<?php
// General code to destroy a user's cookie.

// Don't load header, we don't need an SQL connection.
include_once(__DIR__."/../util/core.php");

UnsetCookies();
unset($user);

?>