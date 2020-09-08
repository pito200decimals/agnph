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


function FormatDate($epoch, $format = DEFAULT_DATE_FORMAT, $time_offset=null) {
    global $user;
    $offset = 0;
    if ($time_offset != null) {
        $offset = $time_offset;
    } else {
        if (isset($user)) {
            $offset = $user['Timezone'];
        } else if (isset($_SESSION['timezone_offset'])) {
            $offset = $_SESSION['timezone_offset'];
        }
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
function GetSiteSetting($key, $default_value="", $fresh=false) {
    static $data_table = null;
    if ($data_table == null || $fresh) {
        $data_table = array();
        if (sql_query_into($result, "SELECT * FROM ".SITE_SETTINGS_TABLE.";", 1)) {
            while ($row = $result->fetch_assoc()) {
                $data_table[$row['Name']] = $row['Value'];
            }
        }
    }
    if (isset($data_table[$key])) return $data_table[$key];
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
    GetSiteSetting($key, $value, true);  // Refresh cache.
}

function GetSiteSettingArray($key) {
    $key_prefix = $key."__";
    $escaped_key_prefix = sql_escape($key_prefix);
    $retval = array();
    if (sql_query_into($result, "SELECT * FROM ".SITE_SETTINGS_TABLE." WHERE Name LIKE '$escaped_key_prefix%';", 1)) {
        while ($row = $result->fetch_assoc()) {
            $k = substr($row['Name'], strlen($key_prefix));
            $retval[$k] = $row['Value'];
        }
    }
    return $retval;
}

function SetSiteSettingArray($key, $value_array) {
    $key_prefix = $key."__";
    $escaped_key_prefix = sql_escape($key_prefix);
    sql_query("DELETE FROM ".SITE_SETTINGS_TABLE." WHERE Name LIKE '$escaped_key_prefix%';");
    $key_values = array();
    foreach ($value_array as $k => $v) {
        $db_key = $key_prefix.$k;
        $escaped_db_key = sql_escape($db_key);
        $escaped_db_value = sql_escape($v);
        $key_values[] = "('$escaped_db_key', '$escaped_db_value')";
    }
    sql_query("INSERT INTO ".SITE_SETTINGS_TABLE." (Name, Value) VALUES ".implode(",", $key_values)." ON DUPLICATE KEY UPDATE Value=VALUES(Value);");
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

function Redirect($url) {
    if (startsWith($url, "http")) {
        header("Location: $url", true, 302);
    } else if (startsWith($url, "/")) {
        header("Location: ".SITE_DOMAIN.$url, true, 302);
    } else {
        // Might not work with IE.
        header("Location: $url", true, 302);
    }
    exit();
}

function MaintenanceError() {
    RenderErrorPage("Site is in read-only mode");
}

function IsMaintenanceMode() {
    return (GetSiteSetting(MAINTENANCE_MODE_KEY, "false") == "true");
}

function CanPerformSitePost() {
    global $user;
    if (!isset($user)) return false;
    if (contains($user['Permissions'], 'A')) return true;
    if (IsMaintenanceMode()) return false;
    return true;
}
function CanLogin($user) {
    if (contains($user['Permissions'], 'A')) return true;
    if (IsMaintenanceMode()) return false;
    return true;
}

function SetHeaderHighlight() {
    global $vars;
    $url = $_SERVER['REQUEST_URI'];
    if ($url == "/" || $url == "") {
        $vars['nav_section'] = "home";
        $vars['_title'] = "AGNPH - Home";
    } else if (startsWith($url, "/forums")) {
        $vars['nav_section'] = "forums";
        $vars['_title'] = "AGNPH - Forums";
    } else if (startsWith($url, "/gallery")) {
        $vars['nav_section'] = "gallery";
        $vars['_title'] = "AGNPH - Gallery";
    } else if (startsWith($url, "/fics")) {
        $vars['nav_section'] = "fics";
        $vars['_title'] = "AGNPH - Fics";
    } else if (startsWith($url, "/oekaki")) {
        $vars['nav_section'] = "oekaki";
        $vars['_title'] = "AGNPH - Oekaki";
    } else if (startsWith($url, "/user/list")) {
        $vars['nav_section'] = "user";
        $vars['_title'] = "AGNPH - Users";
    } else if (preg_match("#^/user/\d+/preferences.*$#", $url)) {
        $vars['nav_section'] = "account_preferences";
        $vars['_title'] = "AGNPH - Preferences";
    } else if (preg_match("#^/user/\d+/mail.*$#", $url)) {
        $vars['nav_section'] = "mail";
        $vars['_title'] = "AGNPH - Messages";
    } else if (preg_match("#^/user/\d+.*$#", $url)) {
        $vars['nav_section'] = "user";
        $vars['_title'] = "AGNPH - Users";
    } else if (startsWith($url, "/user/account/link")) {
        $vars['nav_section'] = "link_account";
        $vars['_title'] = "AGNPH - Account Recovery";
    } else if (startsWith($url, "/about")) {
        $vars['nav_section'] = "about";
        $vars['_title'] = "AGNPH - About";
    } else if (startsWith($url, "/admin")) {
        $vars['nav_section'] = "admin";
        $vars['_title'] = "AGNPH - Admin";
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

function GetTimeMs() {
    return round(microtime(/*get_as_float=*/TRUE) / 1000, 3);
}

function PostDebugTiming($description, $time_ms) {
    global $user;
    global $vars;
    if (!contains($user['Permissions'], 'A')) {
        return;
    }
    $vars['debug_timing'][] = array(
        "description" => $description,
        "time_ms" => $time_ms,
    );
}

function PostSessionDebugTiming($description, $time_ms) {
    global $user;
    if (!contains($user['Permissions'], 'A')) {
        return;
    }
    $_SESSION['debug_timing'][] = array(
        "description" => $description,
        "time_ms" => $time_ms,
    );
}

?>