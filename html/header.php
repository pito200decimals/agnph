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
$is_api = isset($_GET['api']) && $_GET['api'] == "xml";
if ($is_api) {
    header('Content-type: text/xml; charset=utf-8');
} else {
    header('Content-type: text/html; charset=utf-8');
}
header('Cache-Control: max-age=3600');  // 3600-seconds, dunno what's a good value here.

// Include common headers.
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/logging.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/logging.php");
include_once(SITE_ROOT."includes/util/browser.php");
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

$vars['GET'] = $_GET;
$vars['POST'] = $_POST;

FetchUserHeaderVars();
SetHeaderHighlight();
GetUnreadPMCount();

if (isset($user)) {
    // Do nothing if logged in.
} else if ($is_api) {
    // Do nothing for API interface.
} else if (IsRealUser()) {
    // If a guest, maybe block on age.
    MaybeShowAgeGate();
}

// Set up for banner notifications. Initialize session banners if not created yet.
if (!isset($_SESSION['banner_notifications'])) {
    $_SESSION['banner_notifications'] = array();
} else if (sizeof($_SESSION['banner_notifications']) > 0) {
    $vars['banner_notifications'] = $_SESSION['banner_notifications'];
    unset($_SESSION['banner_notifications']);
} else {
    $vars['banner_notifications'] = array();
}

if (isset($user)) {
    $vars['user'] = $user;
}

return;

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
    } else if (isset($_SESSION['Skin'])) {
        $skin = $_SESSION['Skin'];
    } else {
        $skin = DEFAULT_SKIN;
        $_SESSION['Skin'] = $skin;
    }
    // Check for malformed value. Do not allow dots or slashes.
    if (contains($skin, ".") || contains($skin, "/") || contains($skin, "\\")) {
        $skin = DEFAULT_SKIN;
    }
    // Also fetch all possible skins (minus the base skin templates).
    $vars['availableSkins'] = array_filter(array_map("basename", array_filter(glob(SITE_ROOT."skin/*"), "is_dir")), function($skin) { return $skin != BASE_SKIN; });
    if (!in_array($skin, $vars['availableSkins'])) {
        $skin = DEFAULT_SKIN;
    }
    $vars['skin'] = $skin;

    // Use these paths to load template assets. If an expected skin directory does not exist, use base skin directory.
    $skin_dirs = array_filter(array("/skin/$skin/", "/skin/".BASE_SKIN."/"), function($path) { return file_exists(__DIR__.$path); });
    $tpl_base_dirs = array_map(function($path) { return __DIR__.$path; }, $skin_dirs);

    $loader = new Twig_Loader_Filesystem($tpl_base_dirs);
    $twig = new Twig_Environment($loader, array(
        "cache" => SITE_ROOT."skin_template_cache",
    ));
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

function MaybeShowAgeGate() {
    global $AGE_GATE_SECTIONS;
    global $vars;
    // Fallback session cookie for redirect. If this URL is visited, redirect to referring page.
    if (contains($_SERVER['REQUEST_URI'], AGE_GATE_PATH)) {
        // Redirect to destination.
        $_SESSION['age_gate'] = true;
        Redirect($_SERVER['HTTP_REFERER']);
    } else {
        // Visiting the actual page URL. Check if valid client-side cookie.
        if (isset($_COOKIE['confirmed_age']) && $_COOKIE['confirmed_age'] == "true") return;
        if (isset($_SESSION['age_gate']) && $_SESSION['age_gate']) return;
        // Check if page should show age restrictions in the first place.
        $uri = strtolower($_SERVER['REQUEST_URI']);
        foreach ($AGE_GATE_SECTIONS as $section) {
            if (startsWith($uri, "/".strtolower($section))) {
                // Show age gate.
                $vars['confirm_age_url'] = "/".AGE_GATE_PATH."/";
                RenderPage("age_splash.tpl");
                exit();
            }
        }
    }
    // Show page normally.
    return;
}
?>