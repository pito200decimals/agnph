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

function GetDateYear($date_str) {
    $split = mb_split("-", $date_str);
    if (sizeof($split) != 3) return -1;
    return (int)$split[0];
}

function GetDateMonth($date_str) {
    $split = mb_split("-", $date_str);
    if (sizeof($split) != 3) return -1;
    return (int)$split[1];
}

function GetDateDay($date_str) {
    $split = mb_split("-", $date_str);
    if (sizeof($split) != 3) return -1;
    return (int)$split[2];
}

function Get18YearsLaterDateStr($date_str) {
    // DOB is YYYY-MM-DD. Add 18 to year.
    $year = GetDateYear($date_str);
    $month = GetDateMonth($date_str);
    $day = GetDateDay($date_str);
    if ($month == 2 && $day == 29) {
        // 18 years later is not a leap-year.
        $month = 3;
        $day = 1;
    }
    $year += 18;
    return sprintf("%04d-%02d-%02d", $year, $month, $day);
}

function FormatShortDuration($val) {
    // For now, always format in terms of days.
    $days = $val / (24 * 60 * 60);
    return $days."d";
}

function ParseShortDuration($val) {
    $mult = 24 * 60 * 60;
    $val = mb_strtolower($val, "UTF-8");
    if (endsWith($val, 'y')) {
        $mult *= 365;
        $val = mb_substr($val, 0, mb_strlen($val) - 1);
    }
    if (endsWith($val, 'm')) {
        $mult *= 30;
        $val = mb_substr($val, 0, mb_strlen($val) - 1);
    }
    if (endsWith($val, 'w')) {
        $mult *= 7;
        $val = mb_substr($val, 0, mb_strlen($val) - 1);
    }
    if (endsWith($val, 'd')) {
        $mult *= 1;
        $val = mb_substr($val, 0, mb_strlen($val) - 1);
    }
    if (!is_numeric($val)) return -1;
    $val = (int)$val;
    if ($val <= 0) return -1;
    return $val * $mult;
}
?>