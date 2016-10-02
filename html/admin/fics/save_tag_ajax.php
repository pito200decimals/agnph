<?php
// Page handling ajax requests for saving tag edits.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    AJAXErr();
}
ComputePageAccess($user);
if (!$vars['canAdminFics']) {
    AJAXErr();
}

define("TABLE", FICS_TAG_TABLE);
define("ALIAS_TABLE", FICS_TAG_ALIAS_TABLE);
define("IMPLICATION_TABLE", FICS_TAG_IMPLICATION_TABLE);
$TAG_TYPE_MAP = $FICS_TAG_TYPES;
include_once(SITE_ROOT."admin/tags/save_tag_ajax.php");
// $original_alias_tag_id and $new_alias_tag_id are defined, apply here?
if (isset($original_tag_id) && isset($new_alias_tag_id)) {
    if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TAG_TABLE." WHERE TagId=$original_tag_id;", 1)) {
        $num_posts = $result->num_rows;
        if ($num_posts < FICS_ADMIN_TAG_ALIAS_CHANGE_LIMIT) {
            $uid = $user['UserId'];
            $now = time();
            while ($row = $result->fetch_assoc()) {
                $sid = $row['StoryId'];
                sql_query("UPDATE ".FICS_STORY_TAG_TABLE." SET TagId=$new_alias_tag_id WHERE StoryId=$sid AND TagId=$original_tag_id;");
            }
        }
    }
    UpdateTagItemCounts(FICS_TAG_TABLE, FICS_STORY_TAG_TABLE, FICS_STORY_TABLE, "StoryId", "I.ApprovalStatus<>'D'", array($original_tag_id, $new_alias_tag_id));  // Update tag counts on touched tags.
}
UpdateTagItemCounts(FICS_TAG_TABLE, FICS_STORY_TAG_TABLE, FICS_STORY_TABLE, "StoryId", "I.ApprovalStatus<>'D'", array($tag_id));
return;

?>