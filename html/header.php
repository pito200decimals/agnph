<?php
// Standard header that includes all the important files. Will reside in the site root.

// Find the site root directory.
$folder_level = "";
while (!file_exists($folder_level.__FILE__)) {
    $folder_level .= "../";
    if (strlen($folder_level) > 20) {
        die("Could not find site root!");
    }
}
if(!defined("SITE_ROOT")) {
    define("SITE_ROOT", __DIR__."/".$folder_level);
}
unset($folder_level);

// Include common headers.
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
// Authenticate logged-in user.
include_once(SITE_ROOT."includes/auth/auth.php");

// TODO: Set site charset. Unicode?

// Set up site-wide vars for template.
$vars = array();
if (isset($user)) {
    // TODO: Sort out if we want to use the same keys as $user.
    $vars['user'] = $user;
} else {
    // Set up defaults for a guest.
    $vars['user']['display_name'] = "Guest";
    $vars['user']['uid'] = 0;
}

// Set up site-wide defaults.
// TODO: Load from db.
$vars['navigation'] = array(
    array('href' => "/", 'caption' => "Home"),
    array('href' => "/forums/", 'caption' => "Forums"),
    array('href' => "/gallery/", 'caption' => "Gallery"),
    array('href' => "/fics/", 'caption' => "Fics"),
    array('href' => "/oekaki/", 'caption' => "Oekaki"),
    array('href' => "/about/", 'caption' => "About"));
$vars['account_links'] = array();
if (isset($user)) {
    $vars['account_links'][] = array('href' => "/", 'caption' => "Account");
    $vars['account_links'][] = array('href' => "/includes/auth/logout.php", 'caption' => "Log Out");
} else {
    $vars['account_links'][] = array('href' => "/includes/auth/login.php", 'caption' => "Login");
    $vars['account_links'][] = array('href' => "/", 'caption' => "Register");
}

// Template engine includes.
include_once(__DIR__."/../lib/Twig/Autoloader.php");
Twig_Autoloader::register();

// TODO: Get skin preference from db, and set the template dir path.
$skin = "agnph";
//$loader = new Twig_Loader_Filesystem(__DIR__."/skin/agnph/");
$loader = new Twig_Loader_Filesystem(__DIR__."/skin/$skin/");
$twig = new Twig_Environment($loader);
?>