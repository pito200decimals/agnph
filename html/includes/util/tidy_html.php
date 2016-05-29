<?php

function SkipToNextIf(&$html, &$result, $find, $search, $avoid = null, $prefix = null, $suffix = null) {
    if (startsWith($html, $find)) {
        $index = strpos($html, $search);
        if ($index === false) {
            return false;
        }
        if ($avoid != null) {
            $ind = strpos($html, $avoid);
            if ($ind !== false && $ind < $index) {
                return false;
            }
        }
        $index += strlen($search);
        if ($prefix != null) $result .= $prefix;
        $result .= substr($html, 0, $index);
        if ($suffix != null) $result .= $suffix;
        $html = substr($html, $index);
        return true;
    } else {
        return false;
    }
}

function ScanText(&$html, &$result, $stop) {
    while (strlen($html) > 0 && !startsWith($html, $stop)) {
        $result .= substr($html, 0, 1);
        $html = substr($html, 1);
    }
}

function IncTabs($tabs) {
    $tabs .= "  ";
    return $tabs;
}

function DecTabs($tabs) {
    if (strlen($tabs) >= 2) {
        return substr($tabs, 2);
    } else {
        return "";
    }
}

function TidyHTML($html) {
    $it = 0;
    $limit = 100000;  // Safe limit so we don't hang.
    $result = "";
    $tabs = "";
    while (mb_strlen($html) > 0 && $it < $limit) {
        $it++;
        if (SkipToNextIf($html, $result, "<script", "</script>", null, $tabs, "\n")) {
        } elseif (SkipToNextIf($html, $result, "<!--", "-->", null, $tabs, "\n")) {
        } elseif (SkipToNextIf($html, $result, "<?", "?>", null, $tabs, "\n")) {
        } elseif (SkipToNextIf($html, $result, "<!", ">", null, $tabs, "\n")) {
        } elseif (SkipToNextIf($html, $result, "</", ">", null, DecTabs($tabs), "\n")) {
            $tabs = DecTabs($tabs);
        } elseif (SkipToNextIf($html, $result, "<", "/>", ">", $tabs, "\n")) {
        } elseif (SkipToNextIf($html, $result, "<", ">", "/>", $tabs, "\n")) {
            $tabs = IncTabs($tabs);
        } else {
            // Fetch block of text.
            $result .= $tabs;
            ScanText($html, $result, "<");
            $result .= "\n";
        }
    }
    if ($it == $limit) return $html;  // Return unchanged.
    return $result;
}


$TIDY_HTML_SKIP_SPACES_BETWEEN_SINGLE_TAGS = array("img");

// Returns properly-indented HTML.
function TidyHTML_Old($html) {
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