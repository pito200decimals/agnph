<?php
// Utility functions related to mailbox management.

include_once(SITE_ROOT."includes/util/core.php");

// Gets messages to or from a given user. Returns null on failure.
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
        $message['date'] = FormatDate($message['Timestamp'], PROFILE_DATE_TIME_FORMAT);
    }
    return true;
}

// Removes messages that are part of the same conversation, leaving only the latest one.
function BundleMessageThreads(&$messages) {
    // Construct convenient map.
    $msg_by_id = array();
    foreach ($messages as &$msg) {
        $msg_by_id[$msg['Id']] = &$msg;
    }
    ksort($msg_by_id);

    // Construct message forest.
    $forest = array();
    foreach ($msg_by_id as &$msg) {  // Iterate in ID order.
        $id = $msg['Id'];
        $pid = $msg['ParentMessageId'];
        if ($pid == -1) {
            $forest[$id] = $id;
        } else {
            while ($pid != $forest[$pid]) {  // While has a parent message.
                $pid = $forest[$pid];
            }
            $forest[$id] = $pid;
        }
    }
    ksort($forest);

    // Compute latest message in each forest in the forest.
    $latest_id = array();
    $counts = array();
    foreach ($forest as $id => $pid) {
        if (isset($latest_id[$pid])) {
            $counts[$pid]++;
            if ($msg_by_id[$latest_id[$pid]]['Timestamp'] < $msg_by_id[$id]['Timestamp']) {
                $latest_id[$pid] = $id;
            }
        } else {
            $latest_id[$pid] = $id;
            $counts[$pid] = 1;
        }
    }

    // Erase messages that aren't the latest.
    $messages_size = sizeof($messages);
    for ($i = 0; $i < $messages_size; $i++) {
        $msg = &$messages[$i];
        $id = $msg['Id'];
        $root = $forest[$id];
        $msg['count'] = $counts[$root];
        if ($latest_id[$root] != $id) {
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