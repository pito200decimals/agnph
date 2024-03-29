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
include_once(SITE_ROOT."includes/util/user_activity.php");
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
MarkUserVisit();
if (IsBlacklistedURLVisitor() || IsBlacklistedBot()) {
  AJAXErr();
}

$vars['debug'] = DEBUG;
$vars['version'] = VERSION;
$vars['copyright_year'] = date("Y");

// Template engine includes.
include_once(__DIR__."/../lib/Twig/Autoloader.php");
Twig_Autoloader::register();

$vars['GET'] = $_GET;
$vars['POST'] = $_POST;

FetchUserHeaderVars();
SetHeaderHighlight();
GetUnreadPMCount();
GetNotificationCount();

if (isset($user)) {
    // Do nothing if logged in.
} else if ($is_api) {
    // Do nothing for API interface.
} else if (IsRealUser()) {
    // If a guest, maybe block on age.
    MaybeShowAgeGate();
}

// Set up for banner notifications. Initialize session banners if not created yet.
$vars['banner_notifications'] = array();
if (!isset($_SESSION['banner_notifications'])) {
    $_SESSION['banner_notifications'] = array();
} else if (sizeof($_SESSION['banner_notifications']) > 0) {
    $vars['banner_notifications'] = $_SESSION['banner_notifications'];
    unset($_SESSION['banner_notifications']);
}
// Do the same for debug timings.
$vars['debug_timing'] = array();
if (!isset($_SESSION['debug_timing'])) {
    $_SESSION['debug_timing'] = array();
} else if (sizeof($_SESSION['debug_timing']) > 0) {
    $vars['debug_timing'] = $_SESSION['debug_timing'];
    unset($_SESSION['debug_timing']);
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
        $skin_setting = $user['Skin'];
    } else if (isset($_COOKIE['Skin'])) {
        $skin_setting = $_COOKIE['Skin'];
    } else if (isset($_SESSION['Skin'])) {
        $skin_setting = $_SESSION['Skin'];
    } else {
        $skin_setting = DEFAULT_SKIN_SETTING;
        $_SESSION['Skin'] = $skin_setting;
    }
    if ($skin_setting == DEFAULT_SKIN_SETTING) {
        $skin = DEFAULT_SKIN;
    } else {
        $skin = $skin_setting;
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

    // For testing purposes, hide incomplete skins from all users except a specific whitelist.
    $users_to_allow_for_test_skins = array();
    if (!isset($user) || (array_search($user['UserId'], $users_to_allow_for_test_skins) === FALSE)) {
        $skins_to_hide = array();
        foreach($skins_to_hide as $hidden_skin) {
            $key = array_search($hidden_skin, $vars['availableSkins']);
            if($key !== FALSE){
                unset($vars['availableSkins'][$key]);
            }
        }
    }

    // Use these paths to load template assets. If an expected skin directory does not exist, use base skin directory.
    $skin_dirs = array_filter(array("/skin/$skin/", "/skin/".BASE_SKIN."/"), function($path) { return file_exists(__DIR__.$path); });
    $tpl_base_dirs = array_map(function($path) { return __DIR__.$path; }, $skin_dirs);

    $loader = new Twig_Loader_Filesystem($tpl_base_dirs);
    $twig = new Twig_Environment($loader, array(
        "cache" => SITE_ROOT."skin_template_cache",
    ));
    $asset_fn = new Twig_SimpleFunction('asset', function($path) use ($skin_dirs) {
        return GetAssetPath($path, $skin_dirs);
    });
    $twig->addFunction($asset_fn);
    $inline_css_asset_fn = new Twig_SimpleFunction('inline_css_asset', function($path) use ($skin_dirs) {
        return GetAssetContentsInTags($path, $skin_dirs, "<style>", "</style>");
    });
    $twig->addFunction($inline_css_asset_fn);
    $inline_js_asset_fn = new Twig_SimpleFunction('inline_js_asset', function($path) use ($skin_dirs) {
        return GetAssetContentsInTags($path, $skin_dirs, "<script type='text/javascript'>", "</script>");
    });
    $twig->addFunction($inline_js_asset_fn);
}

function GetAssetPath($path, $skin_dirs) {
    foreach ($skin_dirs as $base) {
        $full_path = $base.$path;
        if (endsWith($base, '/') && startsWith($path, '/')) {
            $full_path = substr($base, 0, strlen($base) - 1).$path;
        }
        if (file_exists(__DIR__.$full_path)) return $full_path;
    }
    return $path;
}

function GetAssetContentsInTags($path, $skin_dirs, $open_tag, $close_tag) {
    $filepath = __DIR__.GetAssetPath($path, $skin_dirs);
    if (!file_exists($filepath)) {
        return "";  // Skip inline style.
    }
    $contents = file_get_contents($filepath);
    if ($contents === FALSE) {
        return "";
    }
    return $open_tag.$contents.$close_tag;
}

function GetUnreadPMCount() {
    global $user, $vars;
    if (isset($user)) {
        $uid = $user['UserId'];
        if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".USER_MAILBOX_TABLE." WHERE RecipientUserId=$uid AND Status='U' and MessageType=0;", 1)) {
            $vars['unread_message_count'] = $result->fetch_assoc()['C'];
        }
    }
}

function GetNotificationCount() {
    global $user, $vars;
    if (isset($user)) {
        $uid = $user['UserId'];
        if (sql_query_into($result, "SELECT COUNT(*) AS C FROM ".USER_MAILBOX_TABLE." WHERE RecipientUserId=$uid AND Status='U' and MessageType=1;", 1)) {
            $vars['unread_notification_count'] = $result->fetch_assoc()['C'];
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