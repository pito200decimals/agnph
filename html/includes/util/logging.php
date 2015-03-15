<?php
// General utility functions for performing logging.

function RecordUserIP(&$user) {
    $uid = $user['UserId'];
    $ip = $_SERVER['REMOTE_ADDR'];
    sql_query_into($result, "SELECT KnownIPs FROM ".USER_TABLE." WHERE UserId=$uid;", 1);
    $old_ips_string = $result->fetch_assoc()['KnownIPs'];
    if (strlen($old_ips_string) == 0) {
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
        $escaped_ips = sql_escape($new_ips_string);
        sql_query("UPDATE ".USER_TABLE." SET KnownIPs='$escaped_ips' WHERE UserId=$uid;");
    }
}

?>