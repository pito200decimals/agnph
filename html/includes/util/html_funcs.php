<?php

include_once(SITE_ROOT."includes/util/tidy_html.php");

// Includes functions associated with outputing to templates and processing HTML code.

// Sanitizes input for the allowed html tags and attributes. Returns the sanitized result.
// allowed_html_config is of the form "element1[attr1|attr2],element2", e.g. a[href],p
function SanitizeHTMLTags($html, $allowed_html_config) {
    $html = ParseBBCode($html);
    $html = str_replace("> <", ">&nbsp;<", $html);  // Prevent user-created spaces from disappearing. HTMLPurifier will convert back to space.
    $html = str_replace("<ul><br /><li>", "<ul><li>", $html);
    include_once(SITE_ROOT."../lib/HTMLPurifier/HTMLPurifier.auto.php");
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', $allowed_html_config);
    $purifier = new HTMLPurifier($config);
    $html = $purifier->purify($html);
    $trim_values = array(
        "<p></p>",
        "<p> </p>",
        "<p>&nbsp;</p>",
        "<p>\xc2\xa0</p>",
        "<div></div>",
        "<div> </div>",
        "<div>&nbsp;</div>",
        "<div>\xc2\xa0</div>",
        );
    foreach ($trim_values as $trim) {
        $len = strlen($trim);
        while (startsWith($html, $trim)) {
            $html = substr($html, $len);
        }
        while (endsWith($html, $trim)) {
            $html = substr($html, 0, strlen($html) - $len);
        }
    }
    $html = $purifier->purify($html);
    return $html;
    // TODO: Remove XSS injection attacks (e.g. style background image urls).
}

include_once(SITE_ROOT."../lib/JBBCode/Parser.php");

function ParseBBCode($html) {
    $parser = new JBBCode\Parser();
    $parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
    $builder = new JBBCode\CodeDefinitionBuilder('quote', '<blockquote>{param}</blockquote>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('spoiler', '<span class="spoiler">{param}</span>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('s', '<span class="strikethrough">{param}</span>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('center', '<span class="center">{param}</span>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('list', '<ul>{param}</ul>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('li', '<li>{param}</li>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('sub', '<span class="subscript">{param}</span>');
    $parser->addCodeDefinition($builder->build());
    $builder = new JBBCode\CodeDefinitionBuilder('sup', '<span class="superscript">{param}</span>');
    $parser->addCodeDefinition($builder->build());
    $parser->parse($html);
    $html = $parser->getAsHtml();
    $html = mb_ereg_replace("\\[quote[^\\]]*\\]", "<blockquote>", $html);
    $html = mb_ereg_replace("\\[/quote[^\\]]*\\]", "</blockquote><br />", $html);
    $html = mb_ereg_replace("\\[/?font[^\\]]*\\]", "", $html);
    $html = mb_ereg_replace("\\[/?glow[^\\]]*\\]", "", $html);
    $html = mb_ereg_replace("\\[/?color[^\\]]*\\]", "", $html);
    return $html;
}

function GetSanitizedTextTruncated($text, $allowed_html_config, $max_byte_size, $add_ellipsis=false) {
    $sanitized = SanitizeHTMLTags($text, $allowed_html_config);
    $ellipsis = "";
    if ($add_ellipsis) $ellipsis = "...";
    
    while (strlen($sanitized) > $max_byte_size) {  // Use byte-size here, not mb_char size.
        $text = mb_substr($text, 0, min(mb_strlen($text) - 1, $max_byte_size));
        $sanitized = htmlentities(SanitizeHTMLTags($text.$ellipsis, $allowed_html_config));
    }
    return $sanitized;
}

// Takes a list of items and removes all but the current viewed page.
// Input: List of items, item offset, items/page
// Output: List of items, floor'd offset, $curr_page, $maxpage.
function Paginate(&$items, &$offset, $items_per_page, &$curr_page, &$maxpage) {
    $num_items = sizeof($items);
    $curr_page = floor($offset / $items_per_page) + 1;
    $offset = ($curr_page - 1) * $items_per_page;
    $maxpage = ceil($num_items / $items_per_page);
    $items = array_slice($items, $offset, $items_per_page);
}

// Creates and returns the HTML for a page iterator (e.g. 1 ... 5 6 [7] 8 9 ... 12), with the appropriate links).
// $link_fn is function($index, $current_page) => iterator link $html. Text passed in will be either "#" or "[#]".
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

function ConstructDefaultPageIterator($currpage, $maxpage, $iterator_size, $url_fn) {
    return ConstructPageIterator($currpage, $maxpage, $iterator_size,
        function($i, $current_page) use ($maxpage, $url_fn) {
            if ($i == 0) {
                if ($current_page == 1) {
                    return "<span class='currentpage'>&lt;&lt;</span>";
                } else {
                    $txt = "&lt;&lt;";
                    $i = $current_page - 1;
                }
            } else if ($i == $maxpage + 1) {
                if ($current_page == $maxpage) {
                    return "<span class='currentpage'>&gt;&gt;</span>";
                } else {
                    $txt = "&gt;&gt;";
                    $i = $current_page + 1;
                }
            } else if ($i == $current_page) {
                return "<span class='currentpage'>$i</span>";
            } else {
                $txt = $i;
            }
            $url = $url_fn($i);
            return "<a href='$url'>$txt</a>";
        }, true);
}

// Renders the page to the given template.
function RenderPage($template, $tidy = false) {
    global $twig, $vars;
    $text = mb_ereg_replace("\s+", " ", $twig->render($template, $vars));
    if (DEBUG) {
        echo "\n\n\n\n\n";
        echo "----------------------------------------------------------------------------------------------\n";
    }
    if ($tidy) {
        $text = mb_ereg_replace(">\s+", ">", $text);
        $text = mb_ereg_replace("\s+<", "<", $text);
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

?>