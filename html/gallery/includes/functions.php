<?php
// General functions for the gallery section.

include_once(SITE_ROOT."gallery/includes/image.php");
include_once(SITE_ROOT."gallery/includes/search.php");

function CanUserUploadPost($user) {
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

// Functions for retrieving tags.
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

function GetTagsByName($tag_names, $create_new = false) {
    $tag_names = array_map("SanitizeTagName", $tag_names);
    if ($create_new) {
        $ret = GetTagsByName($tag_names, false);
        $found_tags = array_map(function($val) { return $val['Name']; }, $ret);
        $missing_tags = array_diff($tag_names, $found_tags);
        return $ret + CreateTagsByName($missing_tags);
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

function CreateTagsByName($tag_names) {
    $tag_names = array_map("SanitizeTagName", $tag_names);
    if (sizeof($tag_names) == 0) return array();
    $tag_class = self::TAG_CLASS;
    $joined = implode(",", array_map(function($name) {
        $name = sql_escape($name);
        return "('$name')";
    }, $tag_names));
    if (!sql_query("INSERT INTO ".$tag_class::TAG_TABLE." (".$tag_class::TAG_NAME.") VALUES $joined;")) return null;
    return GetTagsByName($tag_names, false);
}

?>