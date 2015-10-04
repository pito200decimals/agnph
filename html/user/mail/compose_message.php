<?php
// Composes a PM for a user.

include_once("../../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."user/mail/mail_functions.php");

if (!isset($user)) {
    RenderErrorPage("Must be logged in to send messages");
}
include(SITE_ROOT."user/includes/profile_setup.php");
$profile_user = &$vars['profile']['user'];
if (!CanUserSendPMsForUser($user, $profile_user)) {
    RenderErrorPage("Not authorized to send messages");
}

if (isset($_GET['to'])) {
    $name = $_GET['to'];
    if ($name == "__all_users__") {
        $vars['toUser'] = "All Users";
        $vars['toUserId'] = -1;
    } else {
        $escaped_name = sql_escape($name);
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(DisplayName) LIKE UPPER('$escaped_name');", 1)) {
            $to_user = $result->fetch_assoc();
            $vars['toUser'] = $to_user['DisplayName'];
            $vars['toUserId'] = $to_user['UserId'];
        }
    }
}
if (isset($_POST['message'])) {
    $vars['message'] = $_POST['message'];
}

// This is how to output the template.
RenderPage("user/mail/compose_message.tpl");
return;
?>