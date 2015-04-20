<?php
// Includes functions associated with outputing to templates and processing HTML code.

// Sanitizes input for the allowed html tags and attributes. Returns the sanitized result.
// allowed_html_config is of the form "element1[attr1|attr2],element2", e.g. a[href],p
function SanitizeHTMLTags($html, $allowed_html_config) {
    include_once(SITE_ROOT."../lib/HTMLPurifier/HTMLPurifier.auto.php");
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', $allowed_html_config);
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
}

// TODO: Consolidate pagination more.
// Modifies $items and $offset, returns the HTML for the page_iterator.
// $link_fn is function($page_index, $current_page, $max_pages) => HTML.
function Paginate(&$items, &$offset, $items_per_page, $link_fn, $include_arrows = false) {
    $num_items = sizeof($items);
    $curr_page = floor($offset / $items_per_page) + 1;
    $offset = ($curr_page - 1) * $items_per_page;
    $max_pages = ceil($num_items / $items_per_page);
    $items = array_slice($items, $offset, $items_per_page);
    $new_link_fn = function($page_index, $current_page) use ($link_fn, $max_pages) {
        return $link_fn($page_index, $current_page, $max_pages);
    };
    return ConstructPageIterator($curr_page, $max_pages, DEFAULT_PAGE_ITERATOR_SIZE, $new_link_fn, $include_arrows);
}

// Creates and returns the HTML for a page iterator (e.g. 1 ... 5 6 [7] 8 9 ... 12), with the appropriate links).
// $link_fn is function($index, $current_page) => $html. Text passed in will be either "#" or "[#]".
function ConstructPageIterator($currpage, $maxpage, $iterator_size, $link_fn, $include_arrows = false) {
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
            $ret .= $link_fn($i, $currpage);
        } else {
            $ret .= $link_fn($i, $currpage);
        }
    }
    if ($min_val > 2) {
        $ret = $link_fn(1, $currpage)."...$ret";
    }
    if ($max_val < $maxpage - 1) {
        $ret .= "...".$link_fn($maxpage, $currpage);
    }
    if ($include_arrows) {
        $ret = $link_fn(0, $currpage).$ret.$link_fn($maxpage + 1, $currpage);
    }
    return $ret;
}

// Renders the page to the given template.
function RenderPage($template) {
    global $twig, $vars;
    $text = mb_ereg_replace("/\s+/", " ", $twig->render($template, $vars));
    if (DEBUG) {
        echo "\n\n\n\n\n";
        echo "----------------------------------------------------------------------------------------------\n";
        $text = TidyHTML($text);
    }
    echo ($text);
}

function RenderErrorPage($message = "Error") {
    global $vars;
    $vars['error_msg'] = $message;
    RenderPage("base.tpl");
    exit();
}

// Returns properly-indented HTML.
function TidyHTML($html) {
    function Tabs($indent) {
        $tabs = "";
        for ($i = 0; $i < $indent; $i++) {
            $tabs .= "    ";
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
        if (preg_match("@^(<script.*?</script>)(.*)$@i", $html, $match)) {
            // Script tag.
            $tabs = Tabs($indent);
            $ret .= $tabs.$match[1]."\n";
            $html = $match[2];
        } else if (preg_match("@^(<([^/][^>]*[^/]|[^>/]+)>)([^<>]*)(</[^>]+>)(.*)$@", $html, $match)) {
            // Tag with only text in it.
            $tabs = Tabs($indent);
            $ret .= $tabs.$match[1].trim($match[3]).$match[4]."\n";
            $html = $match[5];
        } else if (preg_match("@^([^<]+)(<.*)@", $html, $match)) {
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
        } elseif (preg_match("@^([^<]*)(<?[^>]+>)(.*)@", $html, $match)) {
            // xml
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