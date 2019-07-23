<?php
// Page for receiving email authentication codes and redirecting the user.

include_once("header.php");
include_once(SITE_ROOT."includes/util/logging.php");

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $escaped_code = sql_escape($_GET['code']);
    if (sql_query_into($result, "SELECT * FROM ".SECURITY_EMAIL_TABLE." WHERE Code='$escaped_code';", 1)) {
        $event = $result->fetch_assoc();
        $event_max_time = (int) $event['MaxTimestamp'];
        $now = time();
        // Redirected URL page is responsible for deleting the entry.
        if ($event_max_time >= $now) {
            $url = $event['Redirect'];
            $_SESSION['auth_row'] = $event;
            LogAction("Email code redeemed ($code), redirecting user to '$url'", "");
            Redirect("$url");
        } else {
            RenderErrorPage("Link has expired. Please request your change again.");
        }
    } else {
        InvalidURL();
    }
} else {
    InvalidURL();
}
?>