<?php
// Handles all POST actions for PM system.

define("DEBUG", true);

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
if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    InvalidURL();
}
$uid = (int)$_GET['uid'];
if (!isset($_GET['action'])) {
    InvalidURL();
}
$action = $_GET['action'];
if ($action == "send") {
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
        // TODO: Write success message.
        $_SESSION['mail_send_message'] = "Message sent";
        header("Location: /user/$uid/mail/");
    } else if (isset($_POST['ruid']) && is_numeric($_POST['ruid'])) {
        // Send new message.
        $ruid = (int)$_POST['ruid'];
    } else {
        RenderErrorPage("Unable to send message");
    }
} else {
    InvalidURL();
}

// This is how to output the template.
RenderPage("user/mail/mail.tpl");
return;
?>