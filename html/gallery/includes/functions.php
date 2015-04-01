<?php
// General functions for the gallery section.

include_once(SITE_ROOT."gallery/includes/image.php");
include_once(SITE_ROOT."gallery/includes/search.php");

// Permissions functions.
function CanUserUploadPost($user) {
    return true;
}
function CanUserEditPost($user) {
    return true;
}
function CanUserCreateTags($user) {
    return true;
}
function CanUserUploadNonPending($user) {
    return true;
}
function CanUserAddOrRemoveFromPools($user) {
    return true;
}
function CanUserChangePoolOrdering($user) {
    return true;
}
function CanUserCreateOrDeletePools($user) {
    return true;
}
function CanUserDeletePost($user) {
    return true;
}
function CanUserApprovePost($user) {
    return true;
}

// General path functions.
function GetSiteImagePath($md5, $ext) { return "/".GetImagePath($md5, $ext); }
function GetSystemImagePath($md5, $ext) { return SITE_ROOT.GetImagePath($md5, $ext); }

function GetSiteThumbPath($md5, $ext) { return "/".GetThumbPath($md5, $ext); }
function GetSystemThumbPath($md5, $ext) { return SITE_ROOT.GetThumbPath($md5, $ext); }

function GetSitePreviewPath($md5, $ext) { return "/".GetPreviewPath($md5, $ext); }
function GetSystemPreviewPath($md5, $ext) { return SITE_ROOT.GetPreviewPath($md5, $ext); }

// Gets display name of tag, using spaces.
function TagNameToDisplayName($tag_name) {
    return strtolower(str_replace("_", " ", $tag_name));
}

// Gets tag name of display name, using underscores.
function TagDisplayNameToTagName($tag_name) {
    return strtolower(str_replace(" ", "_", $tag_name));
}

// Replaces spaces with _, and removes all other whitespace.
function SanitizeTagName($name) {
    return preg_replace("/\s+/", "", strtolower(str_replace(" ", "_", $name)));
}

// Gets a parent post id for a given post. If the parent doesn't exist, returns -1.
// Replaces "none" with -1. Replaces self-parenting with -1.
function GetValidParentPostId($parent_post_id, $post_id) {
    if (strtolower($parent_post_id) == "none") return -1;
    if (is_numeric($parent_post_id) && $parent_post_id != $post_id && $parent_post_id > 0) {
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

// Updates a post with the new tags/properties.
function UpdatePost($tag_string, $post_id, $user) {
    $tag_string = CleanTagString($tag_string);
    $tokens = GetTagStringTokens($tag_string, $post_id);
    $descriptors = GetTagDescriptors($tokens, $post_id);
    UpdatePostWithDescriptors($descriptors, $post_id, $user);
    UpdateTagTypes($descriptors, $user);  // Do after creating tags above when setting post tags.
}

function CleanTagString($tag_string) {
    return preg_replace("/[\t\r\n\v\f]+/", "", $tag_string);
}

function GetTagStringTokens($tag_string, $post_id) {
    return array_filter(explode(" ", $tag_string), function($string) { return strlen($string) > 0; });
}

function GetTagDescriptors($tokens, $post_id) {
    $arr = array_map(function($token) use ($post_id) {
        return GetTagDescriptorFromToken($token, $post_id);
    }, $tokens);
    $arr = array_filter($arr, function($elem) { return $elem !== null; });
    return $arr;
}

function GetTagDescriptorFromToken($token, $post_id) {
    $obj = new stdClass();
    if (($index = strpos($token, ":")) !== FALSE) {
        // Possibly has label.
        $label = strtolower(substr($token, 0, $index));
        $tag = substr($token, $index + 1);
    } else {
        $label = "";
        $tag = $token;
    }
    switch ($label) {
        case "rating":
            $obj->label = $label;
            $tagletter = strtolower(substr($tag, 0, 1));
            if ($tagletter == 'e' || $tagletter == 'q' || $tagletter == 's') {
                $obj->tag = $tagletter;
                $obj->isTag = false;
            } else {
                return null;
            }
            break;
        case "parent":
            $obj->label = $label;
            $obj->tag = GetValidParentPostId($tag, $post_id);
            $obj->isTag = false;
            break;
        case "source":
            $obj->label = $label;
            $obj->tag = str_replace(" ", "%20", $tag);
            $obj->isTag = false;
            break;
        case "artist":
        case "character":
        case "copyright":
        case "general":
        case "species":
            $obj->label = $label;
            $obj->tag = strtolower($tag);
            $obj->isTag = true;
            break;
        default:
            $obj->label = "";
            $obj->tag = strtolower($token);
            $obj->isTag = true;
            break;
    }
    return $obj;
}

function UpdateTagTypes($descriptors, $user) {
    $tag_descriptors = array_filter($descriptors, function($desc) { return $desc->isTag; });
    $mapping = array(
        "artist" => "A",
        "character" => "C",
        "copyright" => "D",
        "general" => "G",
        "species" => "S"
    );
    array_map(function($desc) use ($user, $mapping) {
        if (strlen($desc->label) > 0) {
            SetTagTypeByName($desc->tag, $mapping[$desc->label], $user['UserId']);
        }
    }, $tag_descriptors);
}

function UpdatePostWithDescriptors($descriptors, $post_id, $user) {
    $tag_descriptors = array_filter($descriptors, function($desc) { return $desc->isTag; });
    $tag_names = array_map(function($desc) { return $desc->tag; }, $tag_descriptors);
    $properties = array_filter($descriptors, function($desc) { return !$desc->isTag; });
    $log_fields = array();
    if (sizeof($tag_descriptors) > 0) {
        $tags = GetTagsByName($tag_names, CanUserCreateTags($user), $user['UserId']);
        $tag_ids = array_map(function($tag) { return $tag['TagId']; }, $tags);
        $tag_ids_joined = implode(",", $tag_ids);
        if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$post_id;", 0)) {
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
                if (sql_query("DELETE FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$post_id AND TagId IN ($del_tag_ids_joined);")) {
                    $log_fields['TagsRemoved'] = implode(",", $tags_to_remove);
                }
            }
            if (sizeof($tags_to_add) > 0) {
                $post_tag_tuples = implode(",", array_map(function($tag_id) use ($post_id) {
                    return "($post_id,$tag_id)";
                }, $tags_to_add));
                if (sql_query("INSERT INTO ".GALLERY_POST_TAG_TABLE." (PostId, TagId) VALUES $post_tag_tuples;")) {
                    $log_fields['TagsAdded'] = implode(",", $tags_to_add);
                }
            }
        }
    }
    if (sizeof($properties) > 0) {
        $escaped_post_id = sql_escape($post_id);
        if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1)) {
            $post = $result->fetch_assoc();
            $temp_log_lines = array();
            $sql_sets = array();
            foreach ($properties as $prop) {
                switch ($prop->label) {
                    case "rating":
                        if ($post['Rating'] != $prop->tag) $sql_sets[] = "Rating='".sql_escape($prop->tag)."'";
                        break;
                    case "parent":
                        if ($post['ParentPostId'] != $prop->tag) $sql_sets[] = "ParentPostId='".sql_escape($prop->tag)."'";
                        break;
                    case "source":
                        if ($post['Source'] != $prop->tag) $sql_sets[] = "Source='".sql_escape($prop->tag)."'";
                        break;
                    default:
                        continue 2;
                }
                $temp_log_lines[$prop->label] = PropertyDescriptorToLogEntry($prop);
            }
            if (sizeof($sql_sets) > 0 && sql_query("UPDATE ".GALLERY_POST_TABLE." SET ".implode(",", $sql_sets)." WHERE PostId='$escaped_post_id';")) {
                $log_fields['PropertiesChanged'] = trim(implode(" ", $temp_log_lines));
            }
        }
    }
    if (sizeof($log_fields) > 0) {
        // Log change. Just try to log regardless of failure.
        $now = time();
        $user_id = $user['UserId'];
        $keys = implode(", ", array_keys($log_fields));
        $values = implode(",", array_map(function($str) { return "'$str'"; }, array_values($log_fields)));
        sql_query("INSERT INTO ".GALLERY_POST_TAG_HISTORY_TABLE." (PostId, Timestamp, UserId, $keys) VALUES ($post_id, $now, $user_id, $values);");
    }
}

function PropertyDescriptorToLogEntry($descriptor) {
    return str_replace("parent:-1", "parent:none", $descriptor->label.":".$descriptor->tag);
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

?>