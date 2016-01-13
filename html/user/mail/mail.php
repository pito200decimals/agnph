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
    Paginate($messages, $offset, $mail_per_page, $curr_page, $num_max_pages);
    $iterator = ConstructPageIterator($curr_page, $num_max_pages, DEFAULT_PAGE_ITERATOR_SIZE,
        function($i, $current_page) use ($num_max_pages, $url_fn) {
            if ($i == 0) {
                if ($current_page == 1) {
                    return "<span class='currentpage'>&lt;&lt;</span>";
                } else {
                    $txt = "&lt;&lt;";
                    $i = $current_page - 1;
                }
            } else if ($i == $num_max_pages + 1) {
                if ($current_page == $num_max_pages) {
                    return "<span class='currentpage'>&gt;&gt;</span>";
                } else {
                    $txt = "&gt;&gt;";
                    $i = $current_page + 1;
                }
            } else if ($i == $current_page) {
                return "<span class='currentpage'>$i</span>";
            } else {
                $txt = $i;
            }
            $url = $url_fn($i);
            return "<a href='$url'>$txt</a>";
        }, true);
    return $iterator;
}
?>