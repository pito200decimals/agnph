<?php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."admin/includes/functions.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

if (!isset($user)) {
    AJAXErr();
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminFics']) {
    AJAXErr();
    return;
}

UpdateAllTagCounts(FICS_TAG_TABLE, FICS_STORY_TAG_TABLE, FICS_STORY_TABLE, "StoryId", /*PostFilter=*/"I.ApprovalStatus<>'D'", /*TagFilter=*/"TRUE");
return;
?>