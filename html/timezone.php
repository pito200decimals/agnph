<?php
// Page for handling AJAX requests to set timezone offset.

// Authenticate logged-in user.
include_once("includes/auth/auth.php");

if (isset($_POST['offset'])) {
    if (isset($user) && CanPerformSitePost()) {
        // Store offset in user data only if flag is set.
        if ($user['AutoDetectTimezone']) {
            $uid = $user['UserId'];
            $offset = ConvertReceivedOffset($_POST['offset']);
            sql_query("UPDATE ".USER_TABLE." SET Timezone='$offset' WHERE UserId=$uid;");
            $user['Timezone'] = $offset;
        }
    } else {
        // Store offset only in session variables.
        $_SESSION['timezone_offset'] = ConvertReceivedOffset($_POST['offset']);
    }
}

// Convert value in minutes to value in float.
function ConvertReceivedOffset($offset) {
    $float_offset = -$offset / 60.0;
    return sprintf("%.2f", $float_offset);
}
?>