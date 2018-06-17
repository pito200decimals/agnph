<?php
// Common utility functions for adding event notifications.

include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/constants.php");

function AddNotification($user_id, $title, $contents, $sender_id=-1) {
  global $user;
  $now = time();
  $title = sql_escape(SanitizeHTMLTags($title, DEFAULT_ALLOWED_TAGS));
  $contents = sql_escape(SanitizeHTMLTags($contents, DEFAULT_ALLOWED_TAGS));
  $sql = "INSERT INTO ".USER_MAILBOX_TABLE." (SenderUserId, RecipientUserId, Timestamp, Status, Title, Content, MessageType) VALUES ($sender_id, $user_id, $now, 'U', '$title', '$contents', 1);";
  sql_query($sql);
}

?>