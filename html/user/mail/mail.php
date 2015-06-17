<?php
// Account PM inbox page. Views list of messages.
// URL: /user/{user-id}/messages/
// URL: /user/{user-id}/messages/{offset}/
// URL: /user/{user-id}/messages/unread/
// URL: /user/{user-id}/messages/unread/{offset}/
// URL: /user/mail/mail.php?uid={user-id}[&offset=$1][&unread=1]

define("DEBUG", true);

include_once("../../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."user/mail/mail_functions.php");

if (!isset($user)) {
    RenderErrorPage("Please log in to view messages");
}
include(SITE_ROOT."user/includes/profile_setup.php");
$profile_user = &$vars['profile']['user'];
if (!CanUserViewPMs($user, $profile_user)) {
    RenderErrorPage("Not authorized to view messages");
}

$messages = GetMessages($user) or RenderErrorPage("Unable to load messages");
$vars['messages'] = $messages;

// This is how to output the template.
RenderPage("user/mail/mail.tpl");
return;
?>