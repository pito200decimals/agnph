<?php
// Page for receiving email authentication codes and redirecting the user.

include_once("header.php");

if (isset($_GET['code'])) {
    $escaped_code = sql_escape($_GET['code']);
    if (sql_query_into($result, "SELECT * FROM ".SECURITY_EMAIL_TABLE." WHERE Code='$escaped_code';", 1)) {
        $event = $result->fetch_assoc();
        $event_max_time = (int) $event['MaxTimestamp'];
        $now = time();
        if ($event_max_time >= $now) {
            sql_query("DELETE FROM ".SECURITY_EMAIL_TABLE." WHERE Code='$escaped_code';");  // 1-time use code.
            $url = $event['Redirect'];
            $_SESSION['auth_row'] = $event;
            header("Location: $url");
            return;
        }
    }
}
// 404.
InvalidURL();
?>