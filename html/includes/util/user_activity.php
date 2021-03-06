<?php
// PHP functions related to tracking user visits.

include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/browser.php");
include_once(SITE_ROOT."includes/util/sql.php");

// Implementation taken from http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
function getRelativePath($from, $to)
{
    // some compatibility fixes for Windows paths
    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
    $from = str_replace('\\', '/', $from);
    $to   = str_replace('\\', '/', $to);

    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach($from as $depth => $dir) {
        // find first non-matching dir
        if($dir === $to[$depth]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
}

function MarkUserVisit() {
    if (defined("PRETTY_PAGE_NAME")) {
        $page_name = PRETTY_PAGE_NAME;
    } else {
        return;
    }
    if (!IsRealUser()) return;
    if (isset($_GET['api'])) return;
    global $user;
    if (isset($user)) {
        $key = $user['UserId'];
    } else {
        $key = session_id();
    }
    $now = time();
    $page_url = $_SERVER['REQUEST_URI'];
    $php_page = getRelativePath($_SERVER['DOCUMENT_ROOT'], $_SERVER['SCRIPT_FILENAME']);
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $escaped_page_url = sql_escape($page_url);
    $escaped_php_page = sql_escape($php_page);
    $escaped_page_name = sql_escape($page_name);
    $escaped_user_agent = sql_escape($user_agent);
    $ip_addr = $_SERVER['REMOTE_ADDR'];
    sql_query(
        "REPLACE INTO ".USER_VISIT_TABLE."
        (GuestId, VisitTime, VisitIP, PageUrl, PhpPage, PageName, UserAgent)
        VALUES
        ('$key', $now, '$ip_addr', '$escaped_page_url', '$escaped_php_page', '$escaped_page_name', '$escaped_user_agent');");
}

function IsIdUser($id) {
    // PHP Session ids are usually ~26 characters.
    return mb_strlen("$id") <= 20;
}

function GetUserActivityStats() {
    $stats = array();
    $stats['users_online'] = sizeof(GetBrowsingUsers());
    $stats['guests_online'] = GetNumGuests();
    $day = 24*60*60;
    $stats['users_today'] = sizeof(GetBrowsingUsers($day));
    $stats['unique_visits_today'] = sizeof(GetBrowsingUsers($day)) + GetNumGuests($day);
    $stats['newest_member'] = GetNewestMember();
    return $stats;
}

function GetBlacklistUrlSql() {
    include(SITE_ROOT."includes/util/blacklisted_visit_urls.php");
    return "(".implode(" AND ", array_map(function($s) { return "NOT(PageUrl REGEXP '$s')"; }, $BLACKLISTED_VISIT_URL_REGEXES)).")";
}
function GetBlacklistUASql() {
    include(SITE_ROOT."includes/util/blacklisted_visit_urls.php");
    return "(".implode(" AND ", array_map(function($s) { $s = sql_escape($s); return "NOT(UserAgent REGEXP '$s')"; }, $BLACKLISTED_USER_AGENT_REGEXES)).")";
}

function GetNumGuests($duration=null) {
    if ($duration == null) {
        $duration = CONSIDERED_ONLINE_DURATION;
    }
    $time_limit = time() - $duration;
    $blacklisted_url_condition = GetBlacklistUrlSql();
    $blacklisted_user_agent_condition = GetBlacklistUASql();
    if (sql_query_into($result,
        "SELECT COUNT(*) FROM ".USER_VISIT_TABLE." WHERE
        LENGTH(GuestId) > 20 AND
        VisitTime>$time_limit AND
        $blacklisted_url_condition AND
        $blacklisted_user_agent_condition;", 1)) {
        return $result->fetch_assoc()['COUNT(*)'];
    }
    return 0;
}

function GetBrowsingUsers($duration=null) {
    if ($duration == null) {
        $duration = CONSIDERED_ONLINE_DURATION;
    }
    $time_limit = time() - $duration;
    $users = array();
    if (sql_query_into($result, "SELECT * FROM ".USER_VISIT_TABLE." INNER JOIN ".USER_TABLE." ON GuestId=cast(UserId as CHAR) WHERE VisitTime>$time_limit AND HideOnlineStatus=0;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

function GetNewestMember() {
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE Usermode=1 ORDER BY JoinTime DESC LIMIT 1;", 1)) {
        return $result->fetch_assoc();
    }
    return null;
}

function GetCurrentPageviewStats($column="PageUrl", $duration=null) {
    if ($duration != null) {
        $time_limit = time() - $duration;
    } else {
        $time_limit = time() - CONSIDERED_ONLINE_DURATION;
    }
    $blacklisted_url_condition = GetBlacklistUrlSql();
    $blacklisted_user_agent_condition = GetBlacklistUASql();
    $stats = array();
    if (sql_query_into($result,
        "SELECT
        $column,
        COUNT(*) AS C,
        IF(NOT($blacklisted_user_agent_condition), 'UA', IF(NOT($blacklisted_url_condition), 'URL', '')) AS BReason
        FROM ".USER_VISIT_TABLE." WHERE
        VisitTime>$time_limit
        GROUP BY $column, BReason
        ORDER BY C DESC, $column ASC;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $stats[] = array(
                "$column" => $row[$column],
                "Value" => $row[$column],
                "Count" => $row['C'],
                "Blacklisted" => ($row['BReason']!=""),
                "Reason" => $row['BReason'],
            );
        }
    }
    return $stats;
}

?>