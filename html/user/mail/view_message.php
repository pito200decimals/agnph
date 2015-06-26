<?php
// Views a PM for a user.

include_once("../../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
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
if (!isset($_GET['mid']) || !is_numeric($_GET['mid'])) {
    InvalidURL();
}
$mid = (int)$_GET['mid'];

$messages = GetMessages($user) or RenderErrorPage("Unable to load message");
ComputeMessageTrees($messages);
$msg_by_id = GetMessagesById($messages);
$selected_msg = $msg_by_id[$mid];
$root_id = $selected_msg['ParentMessageId'];
$messages = array_filter($messages, function($msg) use ($root_id) { return $msg['ParentMessageId'] == $root_id; });
foreach ($messages as &$msg) {
    $msg['text'] = SanitizeHTMLTags($msg['text'], DEFAULT_ALLOWED_TAGS);
}
$vars['messages'] = $messages;
$vars['message'] = end($messages);

// TODO: Mark message(s) as read.
// TODO: Paginate?

// This is how to output the template.
RenderPage("user/mail/view_message.tpl");
return;
?>