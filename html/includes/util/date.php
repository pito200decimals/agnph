<?php
// Date utility functions.

// Parses a date string from formats MM/DD/YYYY or YYYY-MM-DD to YYYY-MM-DD. Returns null on parse or validation error.
function ParseDate($date_str) {
    $split = mb_split("/", $date_str);
    if (sizeof($split) == 3) {
        $split = array($split[2], $split[0], $split[1]);
    } else if (sizeof($split) == 1) {
        $split = mb_split("-", $date_str);
    }
    if (sizeof($split) != 3) return null;
    if (!is_numeric($split[0])) return null;
    if (!is_numeric($split[1])) return null;
    if (!is_numeric($split[2])) return null;
    $year = (int)$split[0];
    if ($year <= 1900) return null;
    if ($year > 2100) return null;
    $month = (int)$split[1];
    if ($month <= 0 || $month > 12) return null;
    $day = (int)$split[2];
    if ($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12) {
        if ($day <= 0 || $day > 31) return null;
    } else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
        if ($day <= 0 || $day > 30) return null;
    } else {
        if ($day <= 0 || $day > 29) return null;
    }
    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}
?>