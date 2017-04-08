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
    if (!CanPerformSitePost()) MaintenanceError();
    if (!CanUserSendPMsForUser($user, $profile_user)) RenderErrorPage("Not authorized to send messages");
    if (!isset($_POST['message'])) RenderErrorPage("Unable to send message");
    $message = $_POST['message'];
    $message = SanitizeHTMLTags($message, DEFAULT_ALLOWED_TAGS);
    
    if (isset($_POST['rid']) && is_numeric($_POST['rid'])) {
        // Reply to message.
        $rid = (int)$_POST['rid'];
        sql_query_into($result, "SELECT * FROM ".USER_MAILBOX_TABLE." WHERE Id=$rid AND MessageType<>1;", 1) or RenderErrorPage("Unable to send message");
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

        // Ensure recipient exists (Don't send to accounts that have been imported.
        if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$ruid AND Usermode<>0 AND ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION.";", 1)) {
            RenderErrorPage("User does not exist");
        }

        $pmid = $msg['Id'];  // Also equal to $rid.
        $escaped_message = sql_escape(GetSanitizedTextTruncated($message, DEFAULT_ALLOWED_TAGS, MAX_PM_LENGTH));
        $now = time();
        $title = $msg['Title'];
        if (!startsWith($title, "RE:")) {
            $title = "RE: $title";
        }
        $escaped_title = sql_escape(GetSanitizedTextTruncated($title, NO_HTML_TAGS, MAX_PM_TITLE_LENGTH));
        sql_query("INSERT INTO ".USER_MAILBOX_TABLE."
            (SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Title, Content)
            VALUES
            ($suid, $ruid, $pmid, $now, '$escaped_title', '$escaped_message');") or RenderErrorPage("Unable to send message");
        PostMailSessionBanner("Message sent");
        Redirect("/user/$uid/mail/");
    } else if (isset($_POST['to']) &&
        isset($_POST['ruid']) &&
        is_numeric($_POST['ruid']) &&
        isset($_POST['subject'])) {
        // Send new message.
        if ($_POST['ruid'] == -1 && CanUserPMAllUsers($user)) {
            $ruid = -1;
        } else {
            $to_name = sql_escape($_POST['to']);
            sql_query_into($result,
                "SELECT *, (CASE WHEN ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION." THEN 0 ELSE 1 END) AS IsImported
                FROM ".USER_TABLE." WHERE
                DisplayName LIKE '$to_name'
                ORDER BY IsImported ASC;", 1) or RenderErrorPage("Unable to find user: ".$_POST['to']);
            $ruid = (int)$_POST['ruid'];
            $to_uid = $_POST['ruid'];
            if ($ruid != $to_uid) {
                RenderErrorPage("Unable to find user: ".$_POST['to']);
            }
        }

        $title = $_POST['subject'];
        if (mb_strlen($title) == 0) {
            $title = "(no subject)";
        }
        $escaped_title = sql_escape(GetSanitizedTextTruncated($title, NO_HTML_TAGS, MAX_PM_TITLE_LENGTH));
        $escaped_message = sql_escape(GetSanitizedTextTruncated($message, DEFAULT_ALLOWED_TAGS, MAX_PM_LENGTH));
        $now = time();
        $msgs = array();
        if ($ruid == -1) {
            // Send to all users.
            if (sql_query_into($result, "SELECT UserId FROM ".USER_TABLE." WHERE Usermode=1 AND ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION.";", 1)) {
                while ($row = $result->fetch_assoc()) {
                    $ruid = $row['UserId'];
                    $msgs[] = "($uid, $ruid, -1, $now, '$escaped_title', '$escaped_message', 1)";
                }
            } else {
                RenderErrorPage("Unable to send message");
            }
        } else {
            $msgs[] = "($uid, $ruid, -1, $now, '$escaped_title', '$escaped_message', 0)";
        }
        $msgs = implode(",", $msgs);
        sql_query("INSERT INTO ".USER_MAILBOX_TABLE."
            (SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Title, Content, MessageType)
            VALUES $msgs;") or RenderErrorPage("Unable to send message");

        PostMailSessionBanner("Message sent");
        Redirect("/user/$uid/mail/");
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
    PostSessionBanner($msg, $color);
}
?>