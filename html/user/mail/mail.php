<?php
// Account PM inbox page. Views list of messages.
// URL: /user/{user-id}/messages/
// URL: /user/{user-id}/messages/unread/
// URL: /user/mail/mail.php?uid={user-id}[&offset=$1][&unread=1]

define("PRETTY_PAGE_NAME", "User PMs");

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
if (isset($_POST['action'])) {
    if (!CanPerformSitePost()) MaintenanceError();
    switch ($_POST['action']) {
        case "mark-all-read":
            sql_query("UPDATE ".USER_MAILBOX_TABLE." SET Status='R' WHERE RecipientUserId=$uid AND Status='U';");
            // Go back to requesting page.
            Redirect($_SERVER['HTTP_REFERER']);
        default:
            break;
    }
}
$messages = GetMessages($profile_user);
if ($user['GroupMailboxThreads']) {
    // Bundle together messages of the same conversation.
    BundleMessageThreads($profile_user, $messages);
}
if (isset($_GET['unread'])) {
    $messages = FilterOnlyUnreadMessages($messages);
}

if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
    $offset = $_GET['offset'];
} else {
    $offset = 0;
}
if (sizeof($messages) > INBOX_ITEMS_PER_PAGE) {
    $url_fn = function ($index) use ($uid) {
            $offset = ($index - 1) * INBOX_ITEMS_PER_PAGE;
            $url = "/user/$uid/mail/?offset=$offset";
            return $url;
        };
    $iterator = ConstructMailboxIterator($messages, $offset, INBOX_ITEMS_PER_PAGE, $url_fn);
    $vars['iterator'] = $iterator;
}
if (!AddMessageMetadata($profile_user, $messages)) RenderErrorPage("Error while fetching private messages");
$vars['messages'] = $messages;

// This is how to output the template.
RenderPage("user/mail/mail.tpl");
return;

function ConstructMailboxIterator(&$messages, &$offset, $mail_per_page, $url_fn) {
    Paginate($messages, $offset, $mail_per_page, $curr_page, $maxpage);
    $iterator = ConstructDefaultPageIterator($curr_page, $maxpage, DEFAULT_PAGE_ITERATOR_SIZE, $url_fn);
    $iterator_mobile = ConstructDefaultPageIterator($curr_page, $maxpage, DEFAULT_MOBILE_PAGE_ITERATOR_SIZE, $url_fn);
    return "<span class='desktop-only'>$iterator</span><span class='mobile-only'>$iterator_mobile</span>";
}
?>