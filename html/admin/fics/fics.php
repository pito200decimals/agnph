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
    UpdateSetting(FICS_WELCOME_MESSAGE_KEY, SanitizeHTMLTags($_POST['welcome-message'], DEFAULT_ALLOWED_TAGS));
    $changed = true;
}
if (isset($_POST['min-word-count'])) {
    if (is_numeric($_POST['min-word-count']) &&
        ((int)$_POST['min-word-count']) >= 0) {
        UpdateSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, (int)$_POST['min-word-count']);
        $changed = true;
    } else {
        PostBanner("Invalid minimum word count", "red");
    }
}
if ($changed) {
    header("Location: ".$_SERVER['REQUEST_URI']);
    return;
}

// Get settings from table, and populate fields.
// Assume defaults to start.
$vars['welcome_message'] = DEFAULT_FICS_WELCOME_MESSAGE;
$vars['min_word_count'] = DEFAULT_FICS_CHAPTER_MIN_WORD_COUNT;
if (sql_query_into($result, "SELECT * FROM ".FICS_SITE_SETTINGS_TABLE.";", 1)) {
    while ($row = $result->fetch_assoc()) {
        switch ($row['Name']) {
            case FICS_WELCOME_MESSAGE_KEY:
                $vars['welcome_message'] = SanitizeHTMLTags($row['Value'], DEFAULT_ALLOWED_TAGS);
                break;
            case FICS_CHAPTER_MIN_WORD_COUNT_KEY:
                $vars['min_word_count'] = $row['Value'];
                break;
            default:
                break;
        }
    }
}

RenderPage("admin/fics/fics.tpl");
return;

function UpdateSetting($key, $value) {
    $escaped_key = sql_escape($key);
    $escaped_value = sql_escape($value);
    sql_query("INSERT INTO ".FICS_SITE_SETTINGS_TABLE."
        (Name, Value)
        VALUES
        ('$escaped_key', '$escaped_value')
        ON DUPLICATE KEY UPDATE
            Value=VALUES(Value);");
}

?>