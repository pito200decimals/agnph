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

// Join all user preference tables.
function LoadLoggedInUserAllData($uid) {
    static $cache = array();
    if (!isset($cache[$uid])) {
        $result = sql_query("SELECT * FROM ".USER_TABLE." JOIN ".FORUMS_USER_PREF_TABLE." ON ".USER_TABLE.".UserId=".FORUMS_USER_PREF_TABLE.".UserId WHERE ".USER_TABLE.".UserId=$uid;");
        if (!$result) {
            return null;
        }
        $cache[$uid] = $result->fetch_assoc();
    }
    return $cache[$uid];
}

// Loads the user data into the $user array. Returns true on success, false on failure.
function LoadUser($uid, &$user, $fresh = false) {
    $retlist = array();
    $result = LoadUsers(array($uid), $retlist, $fresh);
    if (!$result) return false;
    $user = $retlist[$uid];
    return true;
}

// Loads a bunch of users into the $userlist array. Returns true on success, false on failure.
function LoadUsers($uids, &$userlist, $fresh = false) {
    static $cache = array();
    $ids_to_load = array();
    if ($fresh) {
        $ids_to_load = $uids;
    } else {
        foreach ($uids as $uid) {
            if (isset($cache[$uid])) {
                $userlist[$uid] = $cache[$uid];
            } else {
                $ids_to_load[] = $uid;
            }
        }
    }
    if (sizeof($ids_to_load) > 0) {
        $joined = implode(",", $ids_to_load);
        $result = sql_query("SELECT * FROM ".USER_TABLE." WHERE UserId IN ($joined);");
        if (!$result || $result->num_rows <= 0) {
            debug("LoadUsers SQL failed!");
            debug("Tried to query for UserIds [$joined]");
            return false;
        }
        while ($row = $result->fetch_assoc()) {
            $row['suspended'] = false;
            $uid = $row['UserId'];
            $cache[$uid] = $row;
            $userlist[$uid] = $row;
        }
    }
    return true;
}

function FormatDate($epoch) {
    $dt = new DateTime("@$epoch");
    return $dt->format('Y-m-d H:i:s');
}

// Creates a page iterator (e.g. 1 ... 5 6 [7] 8 9 ... 12), with the appropriate links).
// $link_fn is function($index, $text) => $html. Text passed in will be either "#" or "[#]".
function ConstructPageIterator($currpage, $maxpage, $iterator_size, $link_fn) {
    $min_val = $currpage - $iterator_size;
    $max_val = $currpage + $iterator_size;
    if ($min_val <= 2) {
        // Extend to include min.
        $min_val = 1;
    }
    if ($max_val >= $maxpage - 1) {
        // Extend to include max.
        $max_val = $maxpage;
    }
    $ret = "";
    for ($i = $min_val; $i <= $max_val; $i++) {
        if ($i == $currpage) {
            $ret .= $link_fn($i, "[$i]");
        } else {
            $ret .= $link_fn($i, $i);
        }
    }
    if ($min_val > 2) {
        $ret = $link_fn(1, 1)."...$ret";
    }
    if ($max_val < $maxpage - 1) {
        $ret .= "...".$link_fn($maxpage, $maxpage);
    }
    return $ret;
}

function debug($message) {
    print("<strong>[DEBUG]</strong>: ");
    var_dump($message);
    print("\n<br />");
}

function debug_die($message) {
    print("<strong>[FATAL]</strong>: ");
    var_dump($message);
    print("\n<br />");
    die();
}

function do_or_die($result) {
    if (!$result) {
        debug_die("FAILURE");
    }
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