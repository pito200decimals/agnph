<?php
// File that contains general functions about tag management.


/////////////////////////////////
// String sanitation functions //
/////////////////////////////////

// Gets display name of tag, using spaces.
function TagNameToDisplayName($tag_name) {
    return mb_strtolower(str_replace("_", " ", $tag_name));
}

// Gets tag name of display name, using underscores.
function TagDisplayNameToTagName($tag_name) {
    return mb_strtolower(str_replace(" ", "_", $tag_name));
}

// Replaces spaces with _, and removes all other whitespace.
function SanitizeTagName($name) {
    return mb_ereg_replace("[\s<>\\[\\]{};!@#\$%^&*+=|\\\\\"',?~`]+", "", TagDisplayNameToTagName($name));
}

// Gets rid of newlines and other junk characters and replaces them with spaces.
function CleanTagString($tag_string) {
    return mb_ereg_replace("[\t\r\n\v\f\s]+", " ", $tag_string);
}

// Returns an array of strings, one for each tag token in the input space-separated list of tokens. Does not include empty strings.
function GetTagStringTokens($tag_string) {
    return array_filter(explode(" ", $tag_string), function($string) { return mb_strlen($string) > 0; });
}


//////////////////////////////
// Tag processing functions //
//////////////////////////////

// Gets tag objects by id array. Indexes the returned array by tag id. Returns null on error.
function GetTagsById($tag_table_name, $tag_ids) {
    if (sizeof($tag_ids) == 0) return array();
    $joined = implode(",", array_map(function($id) { return "'".sql_escape($id)."'"; }, $tag_ids));
    $sql = "SELECT * FROM $tag_table_name WHERE TagId IN ($joined);";
    $ret = array();
    if (!sql_query_into($result, $sql, 0)) return null;
    while ($row = $result->fetch_assoc()) {
        $ret[$row['TagId']] = $row;
    }
    return $ret;
}


// Gets and returns an array of tag objects specified by the tag name array. Creates them if the flag is set, with the associated creator user id.
// All created tags will have the 'General' type.
function GetTagsByName($tag_table_name, $tag_names, $create_new = false, $user_id) {
    // Internal helper function.
    // Creates the array of tag names. Assumes that none of them exist yet.
    $CreateTagsByName = function($tag_table_name, $tag_names, $user_id) {
        $tag_names = array_map("SanitizeTagName", $tag_names);
        if (sizeof($tag_names) == 0) return array();
        $joined = implode(",", array_map(function($name) use ($user_id) {
            $name = sql_escape($name);
            return "('$name', $user_id, $user_id)";
        }, array_filter($tag_names, "mb_strlen")));
        if (!sql_query("INSERT INTO $tag_table_name (Name, CreatorUserId, ChangeTypeUserId) VALUES $joined;")) return null;
        return GetTagsByName($tag_table_name, $tag_names, false, $user_id);
    };

    // Start of function GetTagsByName.
    $tag_names = array_map("SanitizeTagName", $tag_names);
    if ($create_new) {
        $ret = GetTagsByName($tag_table_name, $tag_names, false, $user_id);
        if ($ret == null) $ret = array();
        $found_tags = array_map(function($val) { return $val['Name']; }, $ret);
        $missing_tags = array_diff($tag_names, $found_tags);
        $new_tags = $CreateTagsByName($tag_table_name, $missing_tags, $user_id);
        if ($new_tags === null) $new_tags = array();  // Only occurs on SQL insert error.
        return $ret + $new_tags;  // + operator okay because indexed by tag id.
    } else {
        if (sizeof($tag_names) == 0) return array();
        $joined = implode(",", array_map(function($name) { return "'".sql_escape($name)."'"; }, $tag_names));
        $sql = "SELECT * FROM $tag_table_name WHERE Name IN ($joined);";
        $ret = array();
        if (!sql_query_into($result, $sql, 0)) return null;
        while ($row = $result->fetch_assoc()) {
            $ret[$row['TagId']] = $row;
        }
        return $ret;
    }
}

// Returns tag descriptors of the given token array. The filter function is fn(token, label, tag, item_id) => obj{label, tag, isTag}.
function GetTagDescriptors($tokens, $item_id, $tag_descriptor_filter_fn) {
    $arr = array_map(function($token) use ($item_id, $tag_descriptor_filter_fn) {
        if (($index = mb_strpos($token, ":")) !== FALSE) {
            // Possibly has label.
            $label = mb_strtolower(mb_substr($token, 0, $index));
            $tag = mb_substr($token, $index + 1);
        } else {
            $label = "";
            $tag = $token;
        }
        return $tag_descriptor_filter_fn($token, $label, $tag, $item_id);
    }, $tokens);
    $arr = array_filter($arr, function($elem) { return $elem !== null; });
    return $arr;
}

// Updates the tag types for each descriptor in the input that is a tag.
function UpdateTagTypes($tag_table_name, $char_to_tag_type_map, $descriptors, $user) {
    $tag_descriptors = array_filter($descriptors, function($desc) { return $desc->isTag; });
    $mapping = array();
    foreach ($char_to_tag_type_map as $char => $name) {
        $mapping[mb_strtolower($name)] = $char;
    }
    array_map(function($desc) use ($user, $mapping, $tag_table_name) {
        if (mb_strlen($desc->label) > 0) {  // Only update when a label is explicitly specified.
            $tag_name_escaped = sql_escape($desc->tag);
            $type = $mapping[$desc->label];
            $user_id = $user['UserId'];
            $now = time();
            sql_query("UPDATE $tag_table_name SET Type='$type', ChangeTypeUserId=$user_id, ChangeTypeTimestamp=$now WHERE Name='$tag_name_escaped';");
        }
    }, $tag_descriptors);
}
?>