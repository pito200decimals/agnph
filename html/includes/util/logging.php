<?php
// General utility functions for performing logging.

function RecordUserIP(&$user) {
    $uid = $user['UserId'];
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        return;
    }
    if (isset($user) && isset($user['KnownIPs'])) {
        $old_ips_string = $user['KnownIPs'];
    } else {
        if (!sql_query_into($result, "SELECT KnownIPs FROM ".USER_TABLE." WHERE UserId=$uid;", 1)) return;
        $old_ips_string = $result->fetch_assoc()['KnownIPs'];
    }
    if (mb_strlen($old_ips_string) == 0) {
        $prev_ips = array();
    } else {
        $prev_ips = explode(",", $old_ips_string);
    }
    $old_ip_list = $prev_ips;
    // IPs are stored in old->recent order.
    if(($key = array_search($ip, $prev_ips)) !== false) {
        unset($prev_ips[$key]);
    }
    $prev_ips[] = $ip;
    if (sizeof($prev_ips) > 10) {
        unset($prev_ips[0]);
    }
    $new_ips_string = implode(",", $prev_ips);
    if ($old_ips_string != $new_ips_string) {
        $escaped_ips = sql_escape(GetSanitizedTextTruncated($new_ips_string, NO_HTML_TAGS, MAX_KNOWN_IP_STRING_LENGTH));
        sql_query("UPDATE ".USER_TABLE." SET KnownIPs='$escaped_ips' WHERE UserId=$uid;");
    }
}

function LogVerboseAction($action, $section) {
    LogAction($action, $section, 2);
}

function LogAction($action, $section, $verbosity=1) {
    global $user;
    if (isset($user)) {
        $uid = $user['UserId'];
        $timestamp = time();
        $escaped_action = sql_escape(GetSanitizedTextTruncated($action, DEFAULT_ALLOWED_TAGS, MAX_LOG_ACTION_STRING_LENGTH));
        $escaped_section = sql_escape($section);  // Okay to not sanitize this value.
        $escaped_ips = sql_escape(GetSanitizedTextTruncated($new_ips_string, NO_HTML_TAGS, MAX_KNOWN_IP_STRING_LENGTH));
        sql_query("INSERT INTO ".SITE_LOGGING_TABLE." (UserId, Timestamp, Action, Section, Verbosity) VALUES ($uid, $timestamp, '$escaped_action', '$escaped_section', $verbosity);");
    }
}

?>