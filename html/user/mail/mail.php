<?php
// Account PM inbox page. Views list of messages.
// URL: /user/{user-id}/messages/
// URL: /user/{user-id}/messages/unread/
// URL: /user/mail/mail.php?uid={user-id}[&offset=$1][&unread=1]

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

$uid = $profile_user['UserId'];
$messages = GetMessages($profile_user);
if ($user['GroupMailboxThreads']) {
    // Bundle together messages of the same conversation.
    BundleMessageThreads($messages);
}
if (isset($_GET['unread'])) {
    debug($messages);
    $messages = FilterOnlyUnreadMessages($messages);
    debug($messages);
}

if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
    $offset = $_GET['offset'];
} else {
    $offset = 0;
}
if (sizeof($messages) > INBOX_ITEMS_PER_PAGE) {
    $iterator = Paginate($messages, $offset, INBOX_ITEMS_PER_PAGE,
        function($page_index, $current_page, $max_pages) use ($uid) {
            if ($page_index == 0) {
                $url_offset = $page_index * INBOX_ITEMS_PER_PAGE;
                $text = "<<";
            } else if ($page_index == $max_pages + 1) {
                $url_offset = $page_index * INBOX_ITEMS_PER_PAGE;
                $text = ">>";
            } else if ($page_index == $current_page) {
                return "<a>[$page_index]</a>";
            } else {
                $url_offset = ($page_index - 1) * INBOX_ITEMS_PER_PAGE;
                $text = "$page_index";
            }
            return "<a href='/user/$uid/mail/?offset=$url_offset'>$text</a>";
        }, true);
    $vars['iterator'] = $iterator;
}
$vars['messages'] = $messages;

// Set up banners.
// TODO: Fix banners.
$vars['banner_notifications'] = array();
if (isset($_SESSION['mail_send_message'])) {
    $vars['banner_notifications'][] = array(
        "classes" => array("green-banner"),
        "text" => $_SESSION['mail_send_message'],
        "dismissable" => true,
        "strong" => true);
    unset($_SESSION['mail_send_message']);
}

// This is how to output the template.
RenderPage("user/mail/mail.tpl");
return;
?>