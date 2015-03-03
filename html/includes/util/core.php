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

// Loads all user settings. Should be for the currently logged-in user.
function LoadAllUserPreferences($uid, &$user, $fresh = false) {
    $table_list = array(FORUMS_USER_PREF_TABLE);
    return LoadUser($uid, $user, $table_list, $fresh);
}

// Loads the user data into the $user array. Returns true on success, false on failure.
function LoadUser($uid, &$user, $table_list = array(), $fresh = false) {
    $retlist = array();
    $result = LoadUsers(array($uid), $retlist, $table_list, $fresh);
    if (!$result) return false;
    $user = $retlist[$uid];
    return true;
}

// Loads a bunch of users into the $userlist array. Returns true on success, false on failure.
function LoadUsers($uids, &$userlist, $table_list = array(), $fresh = false) {
    static $cache = array();
    $table_list = array_unique($table_list);
    sort($table_list);
    $table_list_key = implode(",", $table_list);
    $ids_to_load = array();
    if ($fresh) {
        $ids_to_load = $uids;
    } else {
        foreach ($uids as $uid) {
            if (isset($cache[$table_list_key]) && isset($cache[$table_list_key][$uid])) {
                $userlist[$uid] = $cache[$table_list_key][$uid];
            } else {
                $ids_to_load[] = $uid;
            }
        }
    }
    if (sizeof($ids_to_load) > 0) {
        $ids = implode(",", array_unique($ids_to_load));
        $sql_table_join = USER_TABLE;
        foreach ($table_list as $table) {
            if ($table != USER_TABLE) {
                $sql_table_join .= " JOIN $table ON ".USER_TABLE.".UserId=$table.UserId";
            }
        }
        $result = sql_query("SELECT * FROM $sql_table_join WHERE ".USER_TABLE.".UserId IN ($ids);");
        if (!$result || $result->num_rows <= 0) {
            debug("LoadUsers SQL failed!");
            debug("Tried to query for UserIds [$ids]");
            return false;
        }
        while ($row = $result->fetch_assoc()) {
            $row['suspended'] = false;
            $uid = $row['UserId'];
            $cache[$table_list_key][$uid] = $row;
            $userlist[$uid] = $row;
        }
    }
    return true;
}

function FormatDate($epoch) {
    $dt = new DateTime("@$epoch");
    return $dt->format('Y-m-d H:i:s');
}

// Modifies $items and $offset, returns the HTML for the page_iterator.
function Paginate(&$items, &$offset, $items_per_page, $link_fn) {
    $num_items = sizeof($items);
    $curr_page = floor($offset / $items_per_page) + 1;
    $offset = ($curr_page - 1) * $items_per_page;
    $max_pages = ceil($num_items / $items_per_page);
    $items = array_slice($items, $offset, $items_per_page);
    return ConstructPageIterator($curr_page, $max_pages, DEFAULT_PAGE_ITERATOR_SIZE, $link_fn);
}

// Creates and returns the HTML for a page iterator (e.g. 1 ... 5 6 [7] 8 9 ... 12), with the appropriate links).
// $link_fn is function($index, $text, $current_page) => $html. Text passed in will be either "#" or "[#]".
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
            $ret .= $link_fn($i, "[$i]", $currpage);
        } else {
            $ret .= $link_fn($i, $i, $currpage);
        }
    }
    if ($min_val > 2) {
        $ret = $link_fn(1, 1, $currpage)."...$ret";
    }
    if ($max_val < $maxpage - 1) {
        $ret .= "...".$link_fn($maxpage, $maxpage, $currpage);
    }
    return $ret;
}

// Sanitizes input for the allowed html tags and attributes. Returns the sanitized result.
// allowed_html_config is of the form "element1[attr1|attr2],element2", e.g. a[href],p
function SanitizeHTMLTags($html, $allowed_html_config) {
    include_once(SITE_ROOT."../lib/HTMLPurifier/HTMLPurifier.auto.php");
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', $allowed_html_config);
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
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