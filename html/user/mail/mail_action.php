<?php
// Handles all POST actions for PM system.

include_once("../../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."user/mail/mail_functions.php");

if (!isset($user)) {
    RenderErrorPage("Must be logged in to send messages");
}
include(SITE_ROOT."user/includes/profile_setup.php");
$profile_user = &$vars['profile']['user'];
$uid = (int)$profile_user['UserId'];

if (!isset($_GET['action'])) {
    InvalidURL();
}
$action = $_GET['action'];
if ($action == "send") {
    if (!CanUserSendPMsForUser($user, $profile_user)) RenderErrorPage("Not authorized to send messages");
    if (!isset($_POST['message'])) RenderErrorPage("Unable to send message");
    $message = $_POST['message'];
    $message = SanitizeHTMLTags($message, DEFAULT_ALLOWED_TAGS);
    
    if (isset($_POST['rid']) && is_numeric($_POST['rid'])) {
        // Reply to message.
        $rid = (int)$_POST['rid'];
        sql_query_into($result, "SELECT * FROM ".USER_MAILBOX_TABLE." WHERE Id=$rid;", 1) or RenderErrorPage("Unable to send message");
        $msg = $result->fetch_assoc();

        if ($msg['SenderUserId'] == $uid) {
            // Outbox message.
            $ruid = $msg['RecipientUserId'];
            $suid = $uid;
        } else if ($msg['RecipientUserId'] == $uid) {
            // Inbox message.
            $ruid = $msg['SenderUserId'];
            $suid = $uid;
        } else {
            RenderErrorPage("Unable to send message");
        }

        $pmid = $msg['Id'];  // Also equal to $rid.
        $escaped_message = sql_escape($message);
        $now = time();
        $title = $msg['Title'];
        if (!startsWith($title, "RE:")) {
            $title = "RE: $title";
        }
        $escaped_title = sql_escape($title);
        sql_query("INSERT INTO ".USER_MAILBOX_TABLE."
            (SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Title, Content)
            VALUES
            ($suid, $ruid, $pmid, $now, '$escaped_title', '$escaped_message');") or RenderErrorPage("Unable to send message");
        PostMailSessionBanner("Message sent");
        header("Location: /user/$uid/mail/");
    } else if (isset($_POST['to']) &&
        isset($_POST['ruid']) &&
        is_numeric($_POST['ruid']) &&
        isset($_POST['subject'])) {
        // Send new message.
        $to_name = sql_escape($_POST['to']);
        sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE DisplayName LIKE '$to_name';", 1) or RenderErrorPage("Unable to find user: ".$_POST['to']);
        $ruid = (int)$_POST['ruid'];
        $to_uid = $_POST['ruid'];
        if ($ruid != $to_uid) {
            RenderErrorPage("Unable to find user: ".$_POST['to']);
        }

        $title = $_POST['subject'];
        if (mb_strlen($title) == 0) {
            $title = "(no subject)";
        }
        $escaped_title = sql_escape($title);
        $escaped_message = sql_escape($message);
        $now = time();
        sql_query("INSERT INTO ".USER_MAILBOX_TABLE."
            (SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Title, Content)
            VALUES
            ($uid, $ruid, -1, $now, '$escaped_title', '$escaped_message');") or RenderErrorPage("Unable to send message");

        PostMailSessionBanner("Message sent");
        header("Location: /user/$uid/mail/");
    } else {
        RenderErrorPage("Unable to send message");
    }
} else {
    InvalidURL();
}

// This is how to output the template.
RenderPage("user/mail/mail.tpl");
return;

function PostMailSessionBanner($msg, $color="green") {
    $_SESSION['banner_notifications'][] = array(
        "classes" => array("green-banner"),
        "text" => $msg,
        "dismissable" => true,
        "strong" => true);
}
?>