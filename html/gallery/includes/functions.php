<?php
// General functions for the gallery section.

include_once(SITE_ROOT."gallery/includes/image.php");
include_once(SITE_ROOT."gallery/includes/search.php");

function CanUserUploadPost($user) {
    return true;
}
function CanUserEditPost($user) {
    return true;
}

function GetSiteImagePath($md5, $ext) {
    return "/".GetImagePath($md5, $ext);
}
function GetSystemImagePath($md5, $ext) {
    return SITE_ROOT.GetImagePath($md5, $ext);
}

function GetSiteThumbPath($md5, $ext) {
    return "/".GetThumbPath($md5, $ext);
}
function GetSystemThumbPath($md5, $ext) {
    return SITE_ROOT.GetThumbPath($md5, $ext);
}

function GetSitePreviewPath($md5, $ext) {
    return "/".GetPreviewPath($md5, $ext);
}
function GetSystemPreviewPath($md5, $ext) {
    return SITE_ROOT.GetPreviewPath($md5, $ext);
}

function TagNameToDisplayName($tag_name) {
    return strtolower(str_replace("_", " ", $tag_name));
}

function TagDisplayNameToTagName($tag_name) {
    return strtolower(str_replace(" ", "_", $tag_name));
}

function SanitizeTagName($name) {
    return strtolower(str_replace(" ", "_", $name));
}

// Does all processing on tag string. Assigns metadata if needed.
function DoAllProcessTagString($tag_string, $post_id, $user_id) {
    // Set actual tags.
    $prefixes = array();
    $tag_names = TagNameListFromTagNameString($tag_string, $prefixes, true);
    $tags = GetTagsByName($tag_names, true, $user_id);
    SetTagsOnPost($tags, $post_id, $user_id);
    // Change tag types if needed.
    SetAllTagTypesFromTagNameString($tag_names, $prefixes, $user_id);

    $meta_prefixes = array();
    $meta_tag_names = TagNameListFromTagNameString($tag_string, $meta_prefixes, false);
    SetPostRatingSourceParent($meta_tag_names, $meta_prefixes, $post_id, $user_id);
}

// Given a space-separated list of tag names and type prefixes, assigns the correct category to them.
// Assumes that only real tags are passed.
function SetAllTagTypesFromTagNameString($tag_names, $prefixes, $user_id) {
    $mapping = array(
        "artist" => "A",
        "character" => "C",
        "copyright" => "D",
        "general" => "G",
        "species" => "S"
    );
    array_map(function($name, $prefix) use ($user_id, $mapping) {
        if (isset($mapping[$prefix])) {
            SetTagTypeByName($name, $mapping[$prefix], $user_id);
        }
        return true;
    }, $tag_names, $prefixes);
}

// Sets the rating/source/parent of the given post, given arrays of names and prefixes.
// Use tag name "none" or -1 on parent to unset. Can't unset source this way.
// Returns true on success, false on failure.
function SetPostRatingSourceParent($tag_names, $prefixes, $post_id, $user_id) {
    $prefix_name_tuple_list = array_map(function($name, $prefix) {
        if ($prefix == "rating") {
            if ($name == 'e' || $name == 'q' || $name == 's') return array($prefix, $name);
        } else if ($prefix == "parent") {
            if (is_numeric($name)) {
                if ($name > 0) return array($prefix, $name);
                else return array($prefix, -1);
            } else if ($name == "none") {
                return array($prefix, -1);
            }
        } else if ($prefix == "source") {
            return array($prefix, $name);
        }
        return array();
    }, $tag_names, $prefixes);
    $prefix_name_tuple_list = array_filter($prefix_name_tuple_list, function($tuple) {
        return sizeof($tuple) > 0;
    });

    if (sizeof($prefix_name_tuple_list) == 0) return true;
    // Get the post.
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId=$post_id;", 1)) return false;
    $post = $result->fetch_assoc();
    $sql_strs = array_filter(array_map(function($tuple) use ($post) {
        if ($tuple[0] == "rating") {
            if ($post['Rating'] == $tuple[1]) return "";
            return "Rating='".sql_escape($tuple[1])."'";
        } else if ($tuple[0] == "parent") {
            if ($post['ParentPostId'] == $tuple[1]) return "";
            $parent_post_id = GetValidParentPostId($tuple[1], $post['PostId']);
            return "ParentPostId='".sql_escape($parent_post_id)."'";
        } else if ($tuple[0] == "source") {
            if ($post['Source'] == $tuple[1]) return "";
            return "Source='".sql_escape($tuple[1])."'";
        }
    }, $prefix_name_tuple_list), function($str) { return strlen($str) > 0; });
    if (sizeof($sql_strs) > 0) {
        $sql_joined = implode(",", $sql_strs);
        if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET $sql_joined WHERE PostId=$post_id;")) return false;

        $new_properties_log_line = implode(" ", array_map(function($tuple) { return implode(":", $tuple); }, $prefix_name_tuple_list));
        $now = time();
        $micro = round(microtime(true) * 1000000) % 1000000;
        sql_query("INSERT INTO ".GALLERY_POST_TAG_HISTORY_TABLE." (PostId, Timestamp, MicroTimestamp, UserId, PropertiesChanged) VALUES ($post_id, $now, $micro, $user_id, '$new_properties_log_line');");
    }
}

// Given a space-separated list of tag names, returns the filtered and sanitized list of names for tag lookups.
// Strips away any prefixes and places them in the passed destination array.
function TagNameListFromTagNameString($tag_name_string, &$prefixes = array(), $keep_only_actual_tags = true) {
    $list = explode(" ", $tag_name_string);
    $list = array_filter($list, function($name) {
        return strlen($name) > 0;
    });
    $prefix_name_tuple_list = array_map(function($name) use ($keep_only_actual_tags){
        if (($index = strpos($name, ":")) === FALSE) {
            // No label.
            return array("", SanitizeTagName($name));
        }
        // Check against static labels.
        $label = strtolower(substr($name, 0, $index));
        $newname = substr($name, $index + 1);
        if (($label == "artist" || $label == "copyright" || $label == "character" || $label == "general" || $label == "species")
            && strlen($newname) > 0) {
            return array($label, SanitizeTagName($newname));
        } else if (($label == "rating" || $label == "parent")
                    && strlen($newname) > 0) {
            // Only allow ratings and parents of nonzero length.
            if ($keep_only_actual_tags) {
                return array();
            } else {
                return array($label, SanitizeTagName($newname));
            }
        } else if ($label == "source") {
            // Allow source of zero length.
            if ($keep_only_actual_tags) {
                return array();
            } else {
                // Don't lowercase this, just remove spaces to prevent tag tokenization.
                // Up to the user to encode URL properly.
                return array($label, str_replace(" ", "%20", $newname));
            }
        } else {
            // No valid label prefixing a name.
            return array("", SanitizeTagName($name));
        }
    }, $list);
    $prefix_name_tuple_list = array_filter($prefix_name_tuple_list, function($tuple) {
        return sizeof($tuple) > 0;
    });
    $ret = array();
    foreach ($prefix_name_tuple_list as $tuple) {
        $prefixes[] = $tuple[0];
        $ret[] = $tuple[1];
    }
    return $ret;
}

// Gets tag objects by id array.
function GetTagsById($tag_ids) {
    if (sizeof($tag_ids) == 0) return array();
    $joined = implode(",", array_map(function($id) { return "'".sql_escape($id)."'"; }, $tag_ids));
    $sql = "SELECT * FROM ".GALLERY_TAG_TABLE." WHERE TagId IN ($joined);";
    $ret = array();
    if (!sql_query_into($result, $sql, 0)) return null;
    while ($row = $result->fetch_assoc()) {
        $ret[$row['TagId']] = $row;
    }
    return $ret;
}

// Gets tag objects by name array. Creates them if the flag is set, with the associated user id.
// All created tags will have the 'General' type.
function GetTagsByName($tag_names, $create_new = false, $user_id) {
    $tag_names = array_map("SanitizeTagName", $tag_names);
    if ($create_new) {
        $ret = GetTagsByName($tag_names, false, $user_id);
        $found_tags = array_map(function($val) { return $val['Name']; }, $ret);
        $missing_tags = array_diff($tag_names, $found_tags);
        $new_tags = CreateTagsByName($missing_tags, $user_id);
        if ($new_tags === null) return $ret;
        return $ret + $new_tags;
    } else {
        if (sizeof($tag_names) == 0) return array();
        $joined = implode(",", array_map(function($name) { return "'".sql_escape($name)."'"; }, $tag_names));
        $sql = "SELECT * FROM ".GALLERY_TAG_TABLE." WHERE Name IN ($joined);";
        $ret = array();
        if (!sql_query_into($result, $sql, 0)) return null;
        while ($row = $result->fetch_assoc()) {
            $ret[$row['TagId']] = $row;
        }
        return $ret;
    }
}

// Creates the array of tag names. Assumes that none of them exist yet.
// Helper function, should not be called outside of this file.
function CreateTagsByName($tag_names, $user_id) {
    $tag_names = array_map("SanitizeTagName", $tag_names);
    if (sizeof($tag_names) == 0) return array();
    $joined = implode(",", array_map(function($name) use ($user_id) {
        $name = sql_escape($name);
        return "('$name', $user_id, $user_id)";
    }, $tag_names));
    if (!sql_query("INSERT INTO ".GALLERY_TAG_TABLE." (Name, CreatorUserId, ChangeTypeUserId) VALUES $joined;")) return null;
    return GetTagsByName($tag_names, false, $user_id);
}

// Sets the list of tags on the given post. Will add or delete tags to set.
function SetTagsOnPost($tags, $post_id, $user_id) {
    $tag_ids = array_map(function($tag) { return $tag['TagId']; }, $tags);
    $tag_ids_joined = implode(",", $tag_ids);
    $result = sql_query("SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$post_id;");
    $tags_to_add = $tag_ids;
    $tags_to_remove = array();
    while ($row = $result->fetch_assoc()) {
        if (($key = array_search($row['TagId'], $tags_to_add)) === FALSE) {
            // Tag to delete.
            $tags_to_remove[] = $row['TagId'];
        } else {
            unset($tags_to_add[$key]);
        }
    }
    $error = false;
    $tags_changed = false;
    if (sizeof($tags_to_remove) > 0) {
        $del_tag_ids_joined = implode(",", $tags_to_remove);
        if (!sql_query("DELETE FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$post_id AND TagId IN ($del_tag_ids_joined);")) $error = true;
        else $tags_changed = true;
    }
    if (sizeof($tags_to_add) > 0) {
        $post_tag_tuples = implode(",", array_map(function($tag_id) use ($post_id) {
            return "($post_id,$tag_id)";
        }, $tags_to_add));
        if (!sql_query("INSERT INTO ".GALLERY_POST_TAG_TABLE." (PostId, TagId) VALUES $post_tag_tuples;")) $error = true;
        else $tags_changed = true;
    }
    if ($tags_changed) {
        // Success, just try to log regardless of failure.
        // TODO: Log when delete is successful but add fails. This isn't a very common case though.
        $now = time();
        $micro = round(microtime(true) * 1000000) % 1000000;
        $tags_to_add_joined = implode(",", $tags_to_add);
        $tags_to_remove_joined = implode(",", $tags_to_remove);
        sql_query("INSERT INTO ".GALLERY_POST_TAG_HISTORY_TABLE." (PostId, Timestamp, MicroTimestamp, UserId, TagsAdded, TagsRemoved) VALUES ($post_id, $now, $micro, $user_id, '$tags_to_add_joined', '$tags_to_remove_joined');");
    }
    return !$error;
}

// Sets the given tag type and records the user id that set it last.
function SetTagTypeById($tag_id, $type, $user_id) {
    if (!sql_query("UPDATE ".GALLERY_TAG_TABLE." SET Type='$type', ChangeTypeUserId=$user_id WHERE TagId=$tag_id;")) return false;
    return true;
}
function SetTagTypeByName($tag_name, $type, $user_id) {
    $tag_name_escaped = sql_escape($tag_name);
    if (!sql_query("UPDATE ".GALLERY_TAG_TABLE." SET Type='$type', ChangeTypeUserId=$user_id WHERE Name='$tag_name_escaped';")) return false;
    return true;
}
function GetValidParentPostId($parent_post_id, $post_id) {
    if (is_numeric($parent_post_id) && $parent_post_id != $post_id) {
        $escaped_parent_post_id = sql_escape($parent_post_id);
        if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_parent_post_id';", 1)) {
            // Parent post id exists.
        } else {
            // Parent post id doesn't exist.
            $parent_post_id = -1;
        }
    } else {
        $parent_post_id = -1;
    }
    return $parent_post_id;
}

?>