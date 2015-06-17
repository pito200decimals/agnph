<?php
// Utility functions related to mailbox management.

include_once(SITE_ROOT."includes/util/core.php");

// Gets messages to or from a given user. Returns null on failure.
function GetMessages($user, $get_unread_only = false) {
    $uid = $user['UserId'];
    $messages = array();
    $unread_clause = $get_unread_only ? " AND Status='U'" : "";
    $sql = "SELECT * FROM ".USER_MAILBOX_TABLE." WHERE SenderUserId=$uid OR (RecipientUserId=$uid$unread_clause);";
    if (sql_query_into($result, $sql, 0)) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
    if (sizeof($messages) > 0) {
        // Process messages.
        if (!AddMessageMetadata($messages, $user)) return null;
    }
    return $messages;
}

// Adds metadata to messages. Returns true on success, false on failure.
function AddMessageMetadata(&$messages, $user) {
    $ids = array_map(function($msg) { return $msg['Id']; }, $messages);
    $senders = array_map(function($msg) { return $msg['SenderUserId']; }, $messages);
    $recipients = array_map(function($msg) { return $msg['RecipientUserId']; }, $messages);
    $all_user_ids = array_unique(array_merge($senders, $recipients));

    $table_list = array(USER_TABLE);
    $all_users = array();
    if (!LoadTableData($table_list, "UserId", $all_user_ids, $all_users)) return false;
    foreach ($messages as &$message) {
        if ($message['SenderUserId'] != $user['UserId']) {
            $message['toFromUser'] = $all_users[$message['SenderUserId']];
            $message['inbox'] = true;
        } else {
            $message['toFromUser'] = $all_users[$message['RecipientUserId']];
            $message['outbox'] = true;
        }
        $message['date'] = FormatDate($message['Timestamp'], PROFILE_DATE_TIME_FORMAT);
    }
    return true;
}
?>