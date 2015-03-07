<?php
// Core utility functions for general php code.

// Debugging print functions.
function debug($message) {
    print("<strong>[DEBUG]</strong>: ");
    var_dump($message);
    print("\n<br />");
}
function debug_die($message) {
    print("<strong>[FATAL]</strong>: ");
    var_dump($message);
    print("\n<br />");
    die();
}
function do_or_die($result) {
    if (!$result) {
        debug_die("FAILURE");
    }
}

include_once(__DIR__."/html_funcs.php");
include_once(__DIR__."/table_data.php");

// Cookie processing functions.
function CookiesExist() {
    return isset($_COOKIE[UID_COOKIE]) && isset($_COOKIE[SALT_COOKIE]);
}
function UnsetCookies() {
    debug("User cookies have been destroyed.");
    setcookie(UID_COOKIE, "", time() - 3600, "/");
    setcookie(SALT_COOKIE, "", time() - 3600, "/");
}

function FormatDate($epoch) {
    $dt = new DateTime("@$epoch");
    return $dt->format('Y-m-d H:i:s');
}

function GetWithDefault($array, $key, $default) {
    if (isset($array) && isset($array[$key])) {
        return $array[$key];
    } else {
        return $default;
    }
}



?>