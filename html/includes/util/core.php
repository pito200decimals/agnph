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

// Source for startsWith and endsWith: http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -mb_strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = mb_strlen($haystack) - mb_strlen($needle)) >= 0 && mb_strpos($haystack, $needle, $temp) !== FALSE);
}
function contains($haystack, $needle) {
    return mb_strpos($haystack, $needle) !== FALSE;
}

function array_first($array) {
    return reset($array);
}
function array_last($array) {
    return end($array);
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


function FormatDate($epoch, $format = DEFAULT_DATE_FORMAT) {
    global $user;
    $offset = 0;
    if (isset($user)) {
        $offset = $user['Timezone'];
    } else if (isset($_SESSION['timezone_offset'])) {
        $offset = $_SESSION['timezone_offset'];
    }
    $epoch += (int)($offset * 60 * 60);
    $dt = new DateTime("@$epoch");
    return $dt->format($format);
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

// Gets or sets a site setting value.
function GetSiteSetting($key, $default_value) {
    if (sql_query_into($result, "SELECT * FROM ".SITE_SETTINGS_TABLE.";", 1)) {
        while ($row = $result->fetch_assoc()) {
            if ($row['Name'] == $key) {
                return $row['Value'];
            }
        }
    }
    return $default_value;
}
function SetSiteSetting($key, $value) {
    $escaped_key = sql_escape($key);
    $escaped_value = sql_escape($value);
    sql_query("INSERT INTO ".SITE_SETTINGS_TABLE."
        (Name, Value)
        VALUES
        ('$escaped_key', '$escaped_value')
        ON DUPLICATE KEY UPDATE
            Value=VALUES(Value);");
}

function DefaultUser() {
    $user = array(
        'DisplayName' => "Guest",
        'UserId' => 0,
        );
    return $user;
}

// Error to be returned on all AJAX failures.
function AJAXErr() {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

// Error to be returned on malformed URL arguments (when not in AJAX scripts).
function InvalidURL() {
    header("HTTP/1.0 404 Not Found");
    // TODO: Custom 404 page here.
    exit();
}

function SetHeaderHighlight() {
    global $vars;
    $url = $_SERVER['REQUEST_URI'];
    if ($url == "/" || $url == "") {
        $vars['nav_section'] = "home";
    } else if (startsWith($url, "/forums")) {
        $vars['nav_section'] = "forums";
    } else if (startsWith($url, "/gallery")) {
        $vars['nav_section'] = "gallery";
    } else if (startsWith($url, "/fics")) {
        $vars['nav_section'] = "fics";
    } else if (preg_match("#^/user/\d+/preferences.*$#", $url)) {
        $vars['nav_section'] = "account_preferences";
    } else if (preg_match("#^/user/\d+/mail.*$#", $url)) {
        $vars['nav_section'] = "mail";
    } else if (preg_match("#^/user/\d+.*$#", $url)) {
        $vars['nav_section'] = "user";
    } else if (startsWith($url, "/about")) {
        $vars['nav_section'] = "about";
    } else if (startsWith($url, "/admin")) {
        $vars['nav_section'] = "admin";
    }
}

function PostBanner($msg, $color, $dismissable = true, $noescape = false) {
    global $vars;
    $vars['banner_notifications'][] = array(
        "classes" => array("$color-banner"),
        "text" => $msg,
        "dismissable" => $dismissable,
        "strong" => true,
        "noescape" => $noescape);
}

function PostSessionBanner($msg, $color, $dismissable = true, $noescape = false) {
    $_SESSION['banner_notifications'][] =  array(
        "classes" => array("$color-banner"),
        "text" => $msg,
        "dismissable" => $dismissable,
        "strong" => true,
        "noescape" => $noescape);
}

?>