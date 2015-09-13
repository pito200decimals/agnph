<?php
// Utility functions related to mailbox management.

include_once(SITE_ROOT."includes/util/core.php");

// Gets messages to or from a given user, in reverse-chronological order. Returns null on failure.
function GetMessages($user) {
    $uid = $user['UserId'];
    $messages = array();
    $sql = "SELECT * FROM ".USER_MAILBOX_TABLE." WHERE (SenderUserId=$uid OR RecipientUserId=$uid) AND Status<>'D' ORDER BY Timestamp DESC, Id DESC;";
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
    foreach ($all_users as &$usr) {
        $usr['avatarURL'] = GetAvatarURL($usr);
    }
    foreach ($messages as &$message) {
        if ($message['SenderUserId'] != $user['UserId']) {
            $message['toFromUser'] = $all_users[$message['SenderUserId']];
            $message['inbox'] = true;
        } else {
            $message['toFromUser'] = $all_users[$message['RecipientUserId']];
            $message['outbox'] = true;
            // Outbox messages can't be seen as unread.
            $message['Status'] = 'R';
        }
        // Format for template.
        $elapsed_time = time() - $message['Timestamp'];
        if ($elapsed_time < 12 * 60 * 60) {  // Less than 12 hours.
            $format = PROFILE_MAIL_DATE_FORMAT_SHORT;
        } else if ($elapsed_time < 6 * 30 * 24 * 60 * 60) {  // Less than 6 months.
            $format = PROFILE_MAIL_DATE_FORMAT_LONG;
        } else {  // Longer than 6 months.
            $format = PROFILE_MAIL_DATE_FORMAT_VERY_LONG;
        }
        $message['date'] = FormatDate($message['Timestamp'], $format);
        $message['user'] = $all_users[$message['SenderUserId']];
        $message['title'] = $message['Title'];
        $message['text'] = $message['Content'];
    }
    return true;
}

// Returns a mapping from id to message reference.
function GetMessagesById(&$messages) {
    $msg_by_id = array();
    foreach ($messages as &$msg) {
        $msg_by_id[$msg['Id']] = &$msg;
    }
    ksort($msg_by_id);
    return $msg_by_id;
}

// Computes the message trees for a set of messages to/from a given user. Sets ParentMessageId to all the same root (lowest id in the tree).
// If a message is its own root, parent id is that message's id (instead of the original -1).
function ComputeMessageTrees(&$messages) {
    // Construct convenient map.
    $msg_by_id = GetMessagesById($messages);

    // Construct message forest.
    $forest = array();
    foreach ($msg_by_id as &$msg) {  // Iterate in ID order.
        $id = $msg['Id'];
        $pid = $msg['ParentMessageId'];
        if ($pid == -1 || $id == $pid) {
            $forest[$id] = $id;
        } else {
            while ($pid != $forest[$pid]) {  // While has a parent message.
                $pid = $forest[$pid];
            }
            $forest[$id] = $pid;
        }
    }
    ksort($forest);

    foreach ($forest as $id => $pid) {
        $msg_by_id[$id]['ParentMessageId'] = $pid;
    }
}

// Removes messages that are part of the same conversation, leaving only the latest one.
function BundleMessageThreads(&$messages) {
    ComputeMessageTrees($messages);
    $msg_by_id = GetMessagesById($messages);

    // Compute latest message in each tree, and how many messages that tree has.
    $latest_ids = array();
    $counts = array();
    foreach ($msg_by_id as $id => $msg) {
        $pid = $msg['ParentMessageId'];
        if (isset($latest_ids[$pid])) {
            $counts[$pid]++;
            if ($msg_by_id[$latest_ids[$pid]]['Timestamp'] < $msg_by_id[$id]['Timestamp']) {
                $latest_ids[$pid] = $id;
            }
        } else {
            $latest_ids[$pid] = $id;
            $counts[$pid] = 1;
        }
    }

    // Erase messages that aren't the latest.
    $messages_size = sizeof($messages);
    for ($i = 0; $i < $messages_size; $i++) {
        $msg = &$messages[$i];
        $id = $msg['Id'];
        $root_id = $msg['ParentMessageId'];
        $msg['count'] = $counts[$root_id];
        if ($latest_ids[$root_id] != $id) {
            unset($messages[$i]);
        }
    }
}

function FilterOnlyUnreadMessages(&$messages) {
    $messages = array_filter($messages, function($msg) {
        return $msg['Status'] == 'U';
    });
    return $messages;
}
?>