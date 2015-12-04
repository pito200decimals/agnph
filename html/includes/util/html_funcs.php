<?php
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
    $text = html_entity_decode($text);
    $sanitized = SanitizeHTMLTags($text, $allowed_html_config);
    $ellipsis = "";
    if ($add_ellipsis) $ellipsis = "...";
    
    while (strlen($sanitized) > $max_byte_size) {  // Use byte-size here, not mb_char size.
        $text = mb_substr($text, 0, min(mb_strlen($text) - 1, $max_byte_size));
        $sanitized = htmlentities(SanitizeHTMLTags($text.$ellipsis, $allowed_html_config));
    }
    return $sanitized;
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
    $text = mb_ereg_replace("\s+", " ", $twig->render($template, $vars));
    $text = mb_ereg_replace(">\s+", ">", $text);
    $text = mb_ereg_replace("\s+<", "<", $text);
    if (DEBUG) {
        echo "\n\n\n\n\n";
        echo "----------------------------------------------------------------------------------------------\n";
        $text = TidyHTML($text);
    } else {
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

$TIDY_HTML_SKIP_SPACES_BETWEEN_SINGLE_TAGS = array("img");

// Returns properly-indented HTML.
function TidyHTML($html) {
    global $TIDY_HTML_SKIP_SPACES_BETWEEN_SINGLE_TAGS;
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
    $lastOpenCloseTag = null;
    $no_space_after_tag_capture_group = "([\.,?!](?=(\s|<)))?";
    while (mb_strlen($html) > 0) {
        $iter++;
        if ($iter > 10000) {
            return $html;
        }
        $match = array();
        if (mb_eregi("^(<script.*?</script>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // Script tag.
            // 1=<script/>
            // 2,3=no_space_after_tag_capture_group
            // 4=$remainder
            $tabs = Tabs($indent);
            $ret .= $tabs.$match[1]."\n";
            $html = $match[4];
            $lastOpenCloseTag = null;
        } else if (mb_eregi("^(<style.*?</style>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // Style tag.
            // 1=<style/>
            // 2,3=no_space_after_tag_capture_group
            // 4=$remainder.
            $tabs = Tabs($indent);
            $ret .= $tabs.$match[1]."\n";
            $html = $match[4];
            $lastOpenCloseTag = null;
        } else if (mb_ereg("^(<([^/][^>]*[^/]|[^>/]+)>$no_space_after_tag_capture_group)([^<>]*)(</[^>]+>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // Tag with only text in it.
            // 1=<tag>+no_space_after_tag_capture_group
            // 2=tag
            // 3,4=no_space_after_tag_capture_group
            // 5=inner_text
            // 6=</tag>
            // 7,8=no_space_after_tag_capture_group
            // 9=$remainder
            $tabs = Tabs($indent);
            $ret .= $tabs.$match[1].trim($match[5]).$match[6]."\n";
            $html = $match[9];
            $lastOpenCloseTag = null;
        } else if (mb_ereg("^([^<]+)(<.*)$", $html, $match)) {
            // Text.
            // 1=text
            // 2=$remainder
            $tabs = Tabs($indent);
            $ret .= $tabs.trim($match[1])."\n";
            $html = $match[2];
            $lastOpenCloseTag = null;
        } elseif (mb_ereg("^([^<]*)(</[^>]+>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // Close tag.
            // 1=text
            // 2=tag+no_space_after_tag_capture_group
            // 3,4=no_space_after_tag_capture_group
            // 5=$remainder
            $indent--;
            $tabs = Tabs($indent);
            if (mb_strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[5];
            $lastOpenCloseTag = null;
        } elseif (mb_ereg("^([^<]*)(<\\?[^>]+>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // xml
            // 1=text
            // 2=tag+no_space_after_tag_capture_group
            // 3,4=no_space_after_tag_capture_group
            // 5=$remainder
            $tabs = Tabs($indent);
            if (mb_strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[5];
            $lastOpenCloseTag = null;
        } elseif (mb_ereg("^([^<]*)(<![^>]+>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // DOCTYPE
            // 1=text
            // 2=tag+no_space_after_tag_capture_group
            // 3,4=no_space_after_tag_capture_group
            // 5=$remainder
            $tabs = Tabs($indent);
            if (mb_strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[5];
            $lastOpenCloseTag = null;
        } elseif (mb_ereg("^([^<]*)(<([^ />]+)( [^>]*)?/>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // OpenClose tag.
            // 1=text
            // 2=tag+no_space_after_tag_capture_group
            // 3=tag name
            // 4=tag attrs+close
            // 5,6=no_space_after_tag_capture_group
            // 7=$remainder
            $tabs = Tabs($indent);
            $tagName = $match[3];
            if ($lastOpenCloseTag != null) {
                if ($lastOpenCloseTag == $tagName && in_array($tagName, $TIDY_HTML_SKIP_SPACES_BETWEEN_SINGLE_TAGS)) {
                    $tabs = "";
                    $ret = mb_substr($ret, 0, mb_strlen($ret) - 1);  // Erase trailing newline.
                }
            }
            $lastOpenCloseTag = $tagName;
            if (mb_strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[7];
        } elseif (mb_ereg("^([^<]*)(<[^>]+>$no_space_after_tag_capture_group)(.*)$", $html, $match)) {
            // Open tag.
            // 1=text
            // 2=tag+no_space_after_tag_capture_group
            // 3,4=no_space_after_tag_capture_group
            // 5=$remainder
            $tabs = Tabs($indent);
            if (mb_strlen($match[1]) > 0) $ret .= $tabs.$match[1]."\n";
            $ret .= $tabs.$match[2]."\n";
            $html = $match[5];
            $lastOpenCloseTag = null;
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