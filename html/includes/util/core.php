<?php
// Core utility functions for general php code.

// Debugging print functions.
function debug($message, $file = null, $line = null) {
    if (!DEBUG) return;
    $header = "[DEBUG]";
    if (isset($file) && isset($line)) {
        $file = basename($file);
        $header .= "@[$file:$line]";
    }
    print("<strong>$header</strong>: ");
    if (is_string($message)) {
        print_r($message);
    } else {
        var_dump($message);
    }
    print("\n<br />");
}
function debug_die($message, $file = null, $line = null) {
    if (!DEBUG) return;
    $header = "[FATAL]";
    if (isset($file) && isset($line)) {
        $file = basename($file);
        $header .= "@[$file:$line]";
    }
    print("<strong>$header</strong>: ");
    if (is_string($message)) {
        print_r($message);
    } else {
        var_dump($message);
    }
    print("\n<br />");
    die();
}
function do_or_die($result, $file = null, $line = null) {
    if (!$result) {
        debug_die("FAILURE", $file, $line);
    }
}

include_once(__DIR__."/html_funcs.php");
include_once(__DIR__."/table_data.php");

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

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
function FormatDuration($seconds) {
    if ($seconds < 60) {
        return "$seconds second".($seconds == 1 ? "" : "s");
    }
    $minutes = (int)($seconds / 60);
    if ($minutes < 60) {
        return "$minutes minute".($minutes == 1 ? "" : "s");
    }
    $hours = (int)($minutes / 60);
    if ($hours < 24) {
        return "$hours hour".($hours == 1 ? "" : "s");
    }
    $days = (int)($hours / 24);
    if ($days < 30) {
        return "$days day".($days == 1 ? "" : "s");
    }
    if ($days < 365) {
        $months = (int)($days / 30);
        return "$months month".($months == 1 ? "" : "s");
    }
    $years = (int)($days / 365);
    return "$years year".($years == 1 ? "" : "s");
}

function GetWithDefault($array, $key, $default) {
    if (isset($array) && isset($array[$key])) {
        return $array[$key];
    } else {
        return $default;
    }
}

function DefaultUser() {
    $user = array(
        'DisplayName' => "Guest",
        'UserId' => 0,
        );
    return $user;
}

?>