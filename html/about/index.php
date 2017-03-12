<?php
// About home page.

define("PRETTY_PAGE_NAME", "About");

include_once("../header.php");

$vars['fics_min_word_count'] = GetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, 1000);

if (isset($_GET['q'])) {
    switch ($_GET['q']) {
        case "about":
            $vars['_title'] = "AGNPH - About";
            RenderPage("about/index.tpl");
            return;
        case "rules":
            $vars['_title'] = "AGNPH - Rules";
            RenderPage("about/rules.tpl");
            return;
        case "staff":
            $vars['_title'] = "AGNPH - Staff";
            RenderPage("about/staff.tpl");
            return;
        case "gallery":
            $vars['_title'] = "AGNPH - Gallery Help";
            RenderPage("about/gallery.tpl");
            return;
        case "irc":
            $vars['_title'] = "AGNPH - IRC";
            RenderPage("about/irc.tpl");
            return;
        case "minecraft":
            $vars['_title'] = "AGNPH - Minecraft";
            RenderPage("about/minecraft.tpl");
            return;
        case "oekaki":
            $vars['_title'] = "AGNPH - Oekaki Help";
            RenderPage("about/oekaki.tpl");
            return;
        case "account":
            $vars['_title'] = "AGNPH - Account Help";
            RenderPage("about/account.tpl");
            return;
        default:
            break;
    }
}
RenderPage("about/index.tpl");
return;
?>