<?php
// Admin page that reverts gallery edits.
// URL: /admin/gallery/revert-edit/
// URL: /admin/gallery/revert_Edit.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/listview.php");
include_once(SITE_ROOT."admin/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Not authorized to access this page");
    return;
}
ComputePageAccess($user);
if (!$vars['canAdminGallery']) {
    DoRedirect();
}

// Start form processing.

if (!isset($_POST['revert-id'])) {
    RenderErrorPage("Invalid input arguments");
    return;
}

$ids = array_unique(array_values($_POST['revert-id']));
if (sizeof($ids) == 0) {
    RenderErrorPage("Nothing to revert");
    return;
}
$joined_ids = implode(",", $ids);
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_HISTORY_TABLE." WHERE Id IN ($joined_ids) ORDER BY Id DESC;", 1) or RenderErrorPage("Error finding edits");

$edits_by_pid = array();
while ($row = $result->fetch_assoc()) {
    $pid = $row['PostId'];
    if (!isset($edits_by_pid[$pid])) {
        $edits_by_pid[$pid] = array();
    }
    $edits_by_pid[$pid][] = $row;
}

$posts_by_pid = array();
if (sizeof($edits_by_pid) > 0) {
    $joined_pids = implode(",", array_keys($edits_by_pid));
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId IN ($joined_pids);", 1)) {
        while ($row = $result->fetch_assoc()) {
            $posts_by_pid[$row['PostId']] = $row;
        }
    }
    $all_tag_ids = array();
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId IN ($joined_pids);", 1)) {
        while ($row = $result->fetch_assoc()) {
            $all_tag_ids[] = $row['TagId'];
            if (!isset($posts_by_pid[$row['PostId']]['tagIds'])) $posts_by_pid[$row['PostId']]['tagIds'] = array();
            $posts_by_pid[$row['PostId']]['tagIds'][] = $row['TagId'];
        }
    }
    $all_current_tags = GetTagsById(GALLERY_TAG_TABLE, $all_tag_ids);
}

// NOTE: When adding more here, make sure edit logging generates them properly.
// Properties able to revert:
// rating:
// parent:
// source:
debug($edits_by_pid);
foreach ($edits_by_pid as $pid => $edits) {
    // Note: Tags being removed may not exist on the post.
    $tags_to_remove = array();
    $tags_to_add = array();
    $new_rating = $posts_by_pid[$pid]['Rating'];
    $new_parent = $posts_by_pid[$pid]['ParentPostId'];
    $new_source = $posts_by_pid[$pid]['Source'];
    foreach ($edits as $edit) {
        $eid = $edit['Id'];
        debug($edit);
        if (mb_strlen($edit['TagsAdded']) > 0) {
            $tags_added = explode(",", $edit['TagsAdded']);
        } else {
            $tags_added = array();
        }
        if (mb_strlen($edit['TagsRemoved']) > 0) {
            $tags_removed = explode(",", $edit['TagsRemoved']);
        } else {
            $tags_removed = array();
        }
        $tags_to_add = array_unique(array_diff(array_merge($tags_to_add, $tags_removed), $tags_added));
        $tags_to_remove = array_unique(array_diff(array_merge($tags_to_remove, $tags_added), $tags_removed));
        // Also process changed properties.
        if (mb_strlen($edit['PropertiesChanged']) > 0) {
            $props = explode(" ", $edit['PropertiesChanged']);
            foreach ($props as $prop) {
                $key = strtok($prop, ":");
                $old_value = GetPreviousProperty($pid, $eid, $key);
                switch ($key) {
                    case "rating":
                        $new_rating = $old_value;
                        break;
                    case "parent":
                        $new_parent = $old_value;
                        break;
                    case "source":
                        $new_source = $old_value;
                        break;
                    default:
                        debug_die("Error processing property: $prop");
                        break;
                }
            }
        }
    }
    debug("Processing post: $pid");
    debug("Reverting tags:");
    debug($tags_to_remove);
    debug("Re-adding tags:");
    debug($tags_to_add);
    debug("Changing rating to: $new_rating");
    debug("Changing parent to: $new_parent");
    debug("Changing source to: $new_source");
    $tags = GetTagsById(GALLERY_TAG_TABLE, array_merge($tags_to_remove, $tags_to_add));
    $tag_string = implode(" ", array_map(function($tag_id) use ($all_current_tags, $tags_to_remove) {
        if (in_array($tag_id, $tags_to_remove)) return "";
        return $all_current_tags[$tag_id]['Name'];
    }, $posts_by_pid[$pid]['tagIds']));
    foreach ($tags_to_add as $tag_id) {
        $tag_string .= " ".$tags[$tag_id]['Name'];
    }
    $tag_string .= " rating:$new_rating parent:$new_parent source:$new_source";
    debug("Final tag string:");
    debug($tag_string);
    UpdatePost($tag_string, $pid, $user);
}

PostSessionBanner("Edits reverted", "green");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit();

function GetPreviousProperty($pid, $eid, $prop_name) {
    if (sql_query_into($result, "SELECT PropertiesChanged FROM ".GALLERY_POST_TAG_HISTORY_TABLE." WHERE PostId=$pid AND Id < $eid AND LENGTH(PropertiesChanged) > 0 ORDER BY Id DESC;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $properties = explode(" ", $row['PropertiesChanged']);
            foreach ($properties as $prop) {
                // Okay here to not use multibyte processing.
                $key = strtok($prop, ":");
                if ($key == $prop_name) {
                    $value = substr($prop, strlen($key) + 1);
                    if ($key == "parent" && $value == "none") $value = -1;
                    if ($key == "source" && $value == null) $value = "";
                    return $value;
                }
            }
        }
    }
    switch ($prop_name) {
        // Get some defaults here.
        case "rating":
            return "q";
        case "parent":
            return -1;
        case "source":
            return "";
        default:
            debug_die("Failed to find property $prop_name in edit history");
            return "";
    }
}
?>