<?php
// Page for handling AJAX requests to set timezone offset.

define("SITE_ROOT", "./");
include_once(SITE_ROOT."ajax_header.php");

if (isset($_POST['offset']) && is_numeric($_POST['offset'])) {
    $offset_str = ConvertReceivedOffset($_POST['offset']);
    // Only store data if session var changed.
    if (ShouldRecordSessionTime($offset_str)) {
        $_SESSION['timezone_offset'] = $offset_str;
    }
    if (ShouldRecordUserTime($offset_str)) {
        // Update user setting if enabled.
        $uid = $user['UserId'];
        sql_query("UPDATE ".USER_TABLE." SET Timezone='$offset_str' WHERE UserId=$uid;");
        $user['Timezone'] = $offset_str;
    }
}

function ShouldRecordSessionTime($offset_str) {
    return !isset($_SESSION['timezone_offset']) || $offset_str != $_SESSION['timezone_offset'];
}

function ShouldRecordUserTime($offset_str) {
    global $user;
    return isset($user) && $user['AutoDetectTimezone'] && CanPerformSitePost() && $offset_str != $user['Timezone'];
}

// Convert value in minutes to value in float. Round to nearest 15-min offset.
function ConvertReceivedOffset($offset) {
    $offset = -(int)$offset;
    $float_offset = round($offset / 15.0) / 4.0;
    return sprintf("%.2f", $float_offset);
}
?>