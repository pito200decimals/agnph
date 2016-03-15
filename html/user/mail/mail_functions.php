<?php
// Utility functions related to mailbox management.

include_once(SITE_ROOT."includes/util/core.php");

// Gets messages to or from a given user, in reverse-chronological order. Returns null on failure.
function GetMessages($profile_user) {
    $uid = $profile_user['UserId'];
    $messages = array();
    $sql = "SELECT Id, SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Status, Title, MessageType FROM ".USER_MAILBOX_TABLE." WHERE ((SenderUserId=$uid AND MessageType<>1) OR RecipientUserId=$uid) AND Status<>'D' ORDER BY Timestamp DESC, Id DESC;";
    if (sql_query_into($result, $sql, 0)) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
    return $messages;
}

function GetAdminUserMetadata() {
    return array(
        "DisplayName" => "Admin",
        "avatarURL" => DEFAULT_AVATAR_PATH);
}

function AddMessageBodyAndMetadata($profile_user, &$messages) {
    $msg_by_id = GetMessagesById($messages);
    $ids = array_map(function($msg) { return $msg['Id']; }, $messages);
    $joined = implode(",", $ids);
    $sql = "SELECT * FROM ".USER_MAILBOX_TABLE." WHERE Id IN ($joined);";
    if (sql_query_into($result, $sql, 1)) {
        while ($row = $result->fetch_assoc()) {
            $msg_by_id[$row['Id']]['Content'] = $row['Content'];
        }
        AddMessageMetadata($profile_user, $messages);
    }
}

// Adds metadata to messages. Returns true on success, false on failure.
function AddMessageMetadata($profile_user, &$messages) {
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
    $all_users[-1] = GetAdminUserMetadata();
    foreach ($messages as &$message) {
        // Determine if outbox, inbox, or notification.
        if ($message['MessageType'] == 1) {
            $message['toFromUser'] = $all_users[$message['SenderUserId']];
            $message['notification'] = true;
        } else {
            if ($message['SenderUserId'] != $profile_user['UserId']) {
                $message['toFromUser'] = $all_users[$message['SenderUserId']];
                $message['inbox'] = true;
            } else {
                $message['toFromUser'] = $all_users[$message['RecipientUserId']];
                $message['outbox'] = true;
                // Outbox messages can't be seen as unread.
                $message['Status'] = 'R';
            }
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
        $message['title'] = SanitizeHTMLTags($message['Title'], NO_HTML_TAGS);
        $message['text'] = SanitizeHTMLTags($message['Content'], DEFAULT_ALLOWED_TAGS);
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
function ComputeMessageTrees($profile_user, &$messages) {
    FixUnthreadedConversations($profile_user, $messages);
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

function FixUnthreadedConversations($profile_user, &$messages) {
    $msg_per_user = array();
    foreach ($messages as &$msg1) {
        if ($msg1['SenderUserId'] != $profile_user['UserId']) {
            $ouid = $msg1['SenderUserId'];
        } else if ($msg1['RecipientUserId'] != $profile_user['UserId']) {
            $ouid = $msg1['RecipientUserId'];
        } else {
            continue;
        }
        if (!isset($msg_per_user[$ouid])) {
            $msg_per_user[$ouid] = array();
        }
        $msg_per_user[$ouid][] = &$msg1;
    }
    foreach ($msg_per_user as $ouid => $msg_list) {
        $last_msg = null;
        for ($i = sizeof($msg_list) - 1; $i >= 0; $i--) {
            $msg2 = &$msg_list[$i];
            if ($msg2['Title'] != "(No Subject)") continue;
            if ($last_msg == null) {
                $last_msg = $msg2;
                continue;
            }
            if ($msg2['ParentMessageId'] == -1 &&
                $msg2['Timestamp'] <= $last_msg['Timestamp'] + UNTHREADED_CONVERSATION_LINK_TIME &&
                $msg2['Title'] == $last_msg['Title']) {
                // Set parent thread id.
                $msg2['ParentMessageId'] = $last_msg['Id'];
            } else {
                $last_msg = $msg2;
            }
        }
    }
}

// Removes messages that are part of the same conversation, leaving only the latest one.
function BundleMessageThreads($profile_user, &$messages) {
    ComputeMessageTrees($profile_user, $messages);
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