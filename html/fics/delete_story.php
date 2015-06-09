<?php
// Page for confirming story deletion.

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to modify story");
    return;
}

if (!isset($_GET['action'])) {
    InvalidURL();
    return;
}
if (!isset($_GET['sid']) || !is_numeric($_GET['sid'])) {
    InvalidURL();
    return;
}
$sid = (int)$_GET['sid'];
$story = GetStory($sid);
if ($story == null) {
    InvalidURL();
    return;
}
$sid = $story['StoryId'];  // Get db value.
$vars['story'] = $story;
if (isset($_POST['confirm'])) {
    // Submit action.
    // TODO: Log action.
    if ($_GET['action'] == "delete" && CanUserDeleteStory($story, $user)) {
        sql_query("UPDATE ".FICS_STORY_TABLE." SET ApprovalStatus='D' WHERE StoryId=$sid;");
        header("Location: /fics/browse/");
        return;
    } else if ($_GET['action'] == "undelete" && CanUserUndeleteStory($story, $user)) {
        sql_query("UPDATE ".FICS_STORY_TABLE." SET ApprovalStatus='A' WHERE StoryId=$sid;");
        header("Location: /fics/story/$sid/");
        return;
    } else {
        RenderErrorPage("Not authorized to modify story");
        return;
    }
} else {
    if ($_GET['action'] == "delete" && CanUserDeleteStory($story, $user)) {
        $vars['button'] = "Delete Story";
    } else if ($_GET['action'] == "undelete" && CanUserUndeleteStory($story, $user)) {
        $vars['button'] = "Un-Delete Story";
    } else {
        RenderErrorPage("Not authorized to modify story");
        return;
    }
}


RenderPage("fics/deletestory.tpl");
return;

?>