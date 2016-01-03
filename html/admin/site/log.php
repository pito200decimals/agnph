<?php
// Main control panel for admin operations.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");
include_once(SITE_ROOT."includes/util/logging.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!isset($_GET['filter'])) {
    InvalidURL();
    return;
}
$filter = $_GET['filter'];
switch ($filter) {
    case "site":
        $perm = "canAdminSite";
        $name = "Site";
        $sql_filter = "";
        break;
    case "forums":
        $perm = "canAdminForums";
        $name = "Forums";
        $sql_filter = "R";
        break;
    case "gallery":
        $perm = "canAdminGallery";
        $name = "Gallery";
        $sql_filter = "G";
        break;
    case "fics":
        $perm = "canAdminFics";
        $name = "Fics";
        $sql_filter = "F";
        break;
    case "oekaki":
        $perm = "canAdminOekaki";
        $name = "Oekaki";
        $sql_filter = "O";
        break;
    default:
        InvalidURL();
        break;
}
if (!$vars[$perm]) {
    DoRedirect();
}

// Check filter settings, and permissions.
if (isset($_GET['verbosity'])) {
    $verbosity = $_GET['verbosity'];
} else if (isset($_GET['verbose'])) {
    $verbosity = 2;
} else {
    $verbosity = 1;
}
if (strlen($sql_filter) > 0) {
    $sql_order = "WHERE Verbosity <= $verbosity AND Section LIKE '%$sql_filter%' ORDER BY Timestamp DESC";
} else if ($verbosity <= 1) {
    $sql_order = "WHERE Verbosity <= $verbosity AND Section='' ORDER BY Timestamp DESC";
} else {
    $sql_order = "WHERE Verbosity <= $verbosity ORDER BY Timestamp DESC";
}
CollectItems(SITE_LOGGING_TABLE, $sql_order, $log_entries, ADMIN_LOG_ENTRIES_PER_PAGE, $iterator, "Error accessing log");
foreach ($log_entries as &$entry) {
    $entry['date'] = FormatDate($entry['Timestamp']);
    switch ($entry['Section']) {
        case "":
            $entry['section'] = "Site";
            break;
        case "R":
            $entry['section'] = "Forums";
            break;
        case "G":
            $entry['section'] = "Gallery";
            break;
        case "F":
            $entry['section'] = "Fics";
            break;
        case "O":
            $entry['section'] = "Oekaki";
            break;
        default:
            $entry['section'] = "Unknown (".$entry['Section'].")";
            break;
    }
}

$vars['log'] = $log_entries;
$vars['iterator'] = $iterator;
$vars['sectionName'] = $name;
$vars['filter'] = $filter;
$url = $_SERVER['REQUEST_URI'];
if (contains($url, "?")) {
    if (contains($url, "verbose")) {
        $vars['verboseLink'] = "$url";
    } else if (endsWith($url, "?")) {
        $vars['verboseLink'] = $url."verbose";
    } else {
        $vars['verboseLink'] = "$url&verbose";
    }
} else {
    $vars['verboseLink'] = "$url?verbose";
}

$vars['admin_section'] = $filter;
RenderPage("admin/$filter/log.tpl");
return;

?>