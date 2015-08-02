<?php
// Page handling ajax requests for saving tag edits.

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    echo json_encode(array());
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminGallery']) {
    echo json_encode(array());
    return;
}

define("TABLE", GALLERY_TAG_TABLE);
define("ALIAS_TABLE", GALLERY_TAG_ALIAS_TABLE);
define("IMPLICATION_TABLE", GALLERY_TAG_IMPLICATION_TABLE);
define("ITEM_TAG_TABLE", GALLERY_POST_TAG_TABLE);
define("ITEM_ID", "PostId");
$TAG_TYPE_MAP = $GALLERY_TAG_TYPES;
include_once(SITE_ROOT."admin/tags/save_tag_ajax.php");
// $original_alias_tag_id and $new_alias_tag_id are defined, apply here?
if (isset($original_tag_id) && isset($new_alias_tag_id)) {
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE TagId=$original_tag_id;", 1)) {
        $num_posts = $result->num_rows;
        if ($num_posts < GALLERY_ADMIN_TAG_ALIAS_CHANGE_LIMIT) {
            // Batch together changes.
            if (sql_query_into($result_batch, "SELECT MAX(BatchId) FROM ".GALLERY_POST_TAG_HISTORY_TABLE.";", 1)) {
                $batch_id = (int)$result_batch->fetch_assoc()['MAX(BatchId)'];
                $batch_id = $batch_id + 1;
            } else {
                $batch_id = 1;
            }
            $uid = $user['UserId'];
            $now = time();
            while ($row = $result->fetch_assoc()) {
                $pid = $row['PostId'];
                sql_query("INSERT INTO ".GALLERY_POST_TAG_HISTORY_TABLE."
                    (PostId, Timestamp, UserId, TagsAdded, TagsRemoved, BatchId)
                    VALUES
                    ($pid, $now, $uid, '$new_alias_tag_id', '$original_tag_id', $batch_id)");
                sql_query("UPDATE ".GALLERY_POST_TAG_TABLE." SET TagId=$new_alias_tag_id WHERE PostId=$pid AND TagId=$original_tag_id;");
            }
        }
    }
}
return;

?>