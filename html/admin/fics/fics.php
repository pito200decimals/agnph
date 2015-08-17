<?php
// Main control panel for admin operations.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminFics']) {
    DoRedirect();
}

$changed = false;
// Try saving settings, if posted.
if (isset($_POST['welcome-message'])) {
    SetSiteSetting(FICS_WELCOME_MESSAGE_KEY, SanitizeHTMLTags($_POST['welcome-message'], DEFAULT_ALLOWED_TAGS));
    $changed = true;
}
if (isset($_POST['min-word-count'])) {
    if (is_numeric($_POST['min-word-count']) &&
        ((int)$_POST['min-word-count']) >= 0) {
        SetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, (int)$_POST['min-word-count']);
        $changed = true;
    } else {
        PostSessionBanner("Invalid minimum word count", "red");
    }
}
if ($changed) {
    PostSessionBanner("Settings changed", "green");
    header("Location: ".$_SERVER['REQUEST_URI']);
    return;
}

// Get settings from table, and populate fields.
// Assume defaults to start.
$vars['welcome_message'] = DEFAULT_FICS_WELCOME_MESSAGE;
$vars['min_word_count'] = DEFAULT_FICS_CHAPTER_MIN_WORD_COUNT;
$vars['welcome_message'] = SanitizeHTMLTags(GetSiteSetting(FICS_WELCOME_MESSAGE_KEY, ""), DEFAULT_ALLOWED_TAGS);
$vars['min_word_count'] = GetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, null);

RenderPage("admin/fics/fics.tpl");
return;

?>