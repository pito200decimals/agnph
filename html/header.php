<?php
// Standard header that includes all the important files. Will reside in the site root.

// Find the site root directory.
$folder_level = "";
while (!file_exists($folder_level.__FILE__)) {
    $folder_level .= "../";
    if (mb_strlen($folder_level) > 20) {
        die("Could not find site root!");
    }
}
if(!defined("SITE_ROOT")) {
    define("SITE_ROOT", __DIR__."/".$folder_level);
}
unset($folder_level);

// Set up charset.
header('Content-type: text/html; charset=utf-8');

// Include common headers.
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/logging.php");
// Authenticate logged-in user.
include_once(SITE_ROOT."includes/auth/auth.php");

// Set up site-wide vars for template.
$vars = array();
if (isset($user)) {
    RecordUserIP($user);
    $vars['user'] = $user;
}

$vars['debug'] = DEBUG;

// Set up site-wide defaults.
// Navigation links.
$vars['navigation'] = array();
if (sql_query_into($result, "SELECT * FROM ".SITE_NAV_TABLE." ORDER BY ItemOrder;", 0)) {
    while ($row = $result->fetch_assoc()) {
        $vars['navigation'][] = array('href' => $row['Link'], 'caption' => $row['Label']);
    }
} else {
    $vars['navigation'][] = array('href' => "/", 'caption' => "Home");
}

// Account management and login links.
$vars['account_links'] = array();
if (isset($user)) {
    $uid = $user['UserId'];
    $vars['account_links'][] = array('href' => "/user/$uid/", 'caption' => "Account");
    $vars['account_links'][] = array('href' => "/logout/", 'caption' => "Log Out");
    $vars['account_links'][] = array('href' => "/includes/auth/logout.php", 'caption' => "DEBUG Logout");
    unset($uid);
} else {
    $vars['account_links'][] = array('href' => "/login/", 'caption' => "Login");
    $vars['account_links'][] = array('href' => "/register/", 'caption' => "Register");
    $vars['account_links'][] = array('href' => "/login/?debug=true", 'caption' => "DEBUG Login");
}

// Template engine includes.
include_once(__DIR__."/../lib/Twig/Autoloader.php");
Twig_Autoloader::register();

if (isset($user)) {
    $skin = $user['Skin'];
} else {
    $skin = DEFAULT_SKIN;
}
$vars['skin'] = $skin;
$vars['skinDir'] = "/skin/$skin";
$loader = new Twig_Loader_Filesystem(__DIR__."/skin/$skin/");
$twig = new Twig_Environment($loader);
?>