<?php
// Standard header that includes all the important files. Will reside in the site root.

session_start();

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
// TODO: Cache control.

// Include common headers.
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/logging.php");
include_once(SITE_ROOT."includes/util/user.php");
// Authenticate logged-in user.
include_once(SITE_ROOT."includes/auth/auth.php");

// Set up site-wide vars for template.
$vars = array();
if (isset($user)) {
    // TODO: Record page viewed, for both users and guests.
    RecordUserIP($user);
    $user['avatarURL'] = GetAvatarURL($user);
    // Check if admin tab should be visible.
    if (ShouldShowAdminTab($user)) {
        $user['showAdminTab'] = true;
    }
    $vars['user'] = &$user;
    $now = time();
    if ($user['LastVisitTime'] + REFRESH_ONLINE_TIMEOUT < $now) {
        sql_query("UPDATE ".USER_TABLE." SET LastVisitTime=$now WHERE UserId=".$user['UserId'].";");
    }
}

$vars['debug'] = DEBUG;
$vars['version'] = VERSION;

// Template engine includes.
include_once(__DIR__."/../lib/Twig/Autoloader.php");
Twig_Autoloader::register();
// Set up for banner notifications. Initialize session banners if not created yet.
if (!isset($_SESSION['banner_notifications'])) {
    $_SESSION['banner_notifications'] = array();
} else if (sizeof($_SESSION['banner_notifications']) > 0) {
    $vars['banner_notifications'] = $_SESSION['banner_notifications'];
    unset($_SESSION['banner_notifications']);
} else {
    $vars['banner_notifications'] = array();
}
$vars['GET'] = $_GET;
$vars['POST'] = $_POST;

FetchUserHeaderVars();
SetHeaderHighlight();
GetUnreadPMCount();


function FetchUserHeaderVars() {
    global $user, $vars, $twig;
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
    // User skin preferences.
    if (isset($user)) {
        $skin = $user['Skin'];
    } else {
        $skin = DEFAULT_SKIN;
    }
    $vars['skin'] = $skin;

    // Use these paths to load template assets.
    $skin_dirs = array("/skin/$skin/", "/skin/".BASE_SKIN."/");
    $tpl_base_dirs = array_map(function($path) { return __DIR__.$path; }, $skin_dirs);

    $loader = new Twig_Loader_Filesystem($tpl_base_dirs);
    $twig = new Twig_Environment($loader);
    $asset_fn = new Twig_SimpleFunction('asset', function($path) use ($skin_dirs) {
        foreach ($skin_dirs as $base) {
            $full_path = $base.$path;
            if (endsWith($base, '/') && startsWith($path, '/')) {
                $full_path = substr($base, 0, strlen($base) - 1).$path;
            }
            if (file_exists(__DIR__.$full_path)) return $full_path;
        }
        return $path;
    });
    $twig->addFunction($asset_fn);
}

function GetUnreadPMCount() {
    global $user, $vars;
    if (isset($user)) {
        $uid = $user['UserId'];
        if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".USER_MAILBOX_TABLE." WHERE RecipientUserId=$uid AND Status='U';", 1)) {
            $vars['unread_message_count'] = $result->fetch_assoc()['C'];
        }
    }
}
?>