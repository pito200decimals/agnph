<?php
// Core utility functions for general php code.

function CookiesExist() {
    return isset($_COOKIE[UID_COOKIE]) && isset($_COOKIE[SALT_COOKIE]);
}

function UnsetCookies() {
    debug("User cookies have been destroyed.");
    setcookie(UID_COOKIE, "", time() - 3600, "/");
    setcookie(SALT_COOKIE, "", time() - 3600, "/");
}

// Loads the user data into the $user array.
function LoadUser($uid, &$user) {
    // TODO: Load user from db.
    $user['uid'] = $uid;
    $user['display_name'] = "Username $uid";
    $user['email'] = "Email $uid";
    $user['password'] = md5("Password $uid");
    $user['suspended'] = false;
}

function debug($message) {
    print("<strong>[DEBUG]</strong>: ");
    print_r($message);
    print("\n<br />");
}

function debug_die($message, $file, $line) {
    print("<strong>[FATAL]</strong>: ");
    print_r($message);
    print("\n<br />");
    die();
}

function RenderPage($template) {
    global $twig, $vars;
    echo TidyHTML($twig->render($template, $vars));
}

// Returns properly-indented HTML.
function TidyHTML($html) {
    function Tabs($indent) {
        $tabs = "";
        for ($i = 0; $i < $indent; $i++) {
            $tabs .= "  ";
        }
        return $tabs;
    }
    
    $html = str_replace("\n", "", $html);
    $ret = "";
    $indent = 0;
    $iter = 0;
    while (strlen($html) > 0) {
        $iter++;
        if ($iter > 1000) {
            return $html;
        }
        $match = array();
        //print_r("($indent)-----------------------------------------------------------------------------\n$html\n$ret\n");
        if (preg_match("@^([^<]+)(<.*)@", $html, $match)) {
            // Text.
            $tabs = Tabs($indent);
            $ret .= $tabs.trim($match[1])."\n";
            $html = $match[2];
        } elseif (preg_match("@^([^<]*)(</[^>]+>)(.*)@", $html, $match)) {
            // Close tag.
            $indent--;
            $tabs = Tabs($indent);
            if (strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[3];
        } elseif (preg_match("@^([^<]*)(<![^>]+>)(.*)@", $html, $match)) {
            // DOCTYPE
            $tabs = Tabs($indent);
            if (strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[3];
        } elseif (preg_match("@^([^<]*)(<[^>]+/>)(.*)@", $html, $match)) {
            // OpenClose tag.
            $tabs = Tabs($indent);
            if (strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[3];
        } elseif (preg_match("@^([^<]*)(<[^>]+>)(.*)@", $html, $match)) {
            // Open tag.
            $tabs = Tabs($indent);
            if (strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[3];
            $indent++;
        } else {
            // Catch-all and quit.
            $ret .= $html;
            $html = "";
        }
    }
    return $ret;
}

?>