<?php
// General functions for the gallery section.

include_once(SITE_ROOT."gallery/includes/image.php");
include_once(SITE_ROOT."gallery/includes/search.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/comments/comments_functions.php");

// Permissions functions.
function GalleryEditRegistrationPeriodNotOver($user) {
    return $user['JoinTime'] + ALLOW_GALLERY_EDITS_AFTER_REGISTRATION_DEADLINE > time();
}
function CanUserUploadPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    return true;
}
function CanUserUploadWithHiddenUserId($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}
function CanUserEditGalleryPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    return true;
}
function CanUserCreateGalleryTags($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    return true;
}
function CanUserUploadNonPending($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    return false;
}
function CanUserAddToPools($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    // Normal users can add to pools.
    return true;
}
function CanUserRemoveFromPools($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    // Normal users can't remove from pools.
    return false;
}
function CanUserChangePoolOrdering($user) {
    if (!IsUserActivated($user)) return false;
    // Don't allow reordering if posts can be missing from pool view.
    if (!$user['IgnoreGalleryBlacklistForPools']) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    return true;
}
function CanUserCreatePool($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    return true;
}
function CanUserDeletePool($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    return false;
}
function CanUserCreateOrDeletePools($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    return false;
}
function CanUserApprovePost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    return false;
}
function CanUserFlagGalleryPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    // Restrict user based on permissions and time since registration.
    if ($user['GalleryPermissions'] == 'R') return false;
    if (GalleryEditRegistrationPeriodNotOver($user)) return false;
    return true;
}
function CanUserUnflagGalleryPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    return false;
}
function CanUserDeleteGalleryPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}
function CanUserUndeleteGalleryPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}
function CanUserCommentOnPost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'R') return false;
    return true;
}
function CanUserDeleteGalleryComment($user, $comment) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    // TODO: Allow users to delete their own comments?
    // if ($user['UserId'] == $comment['UserId']) return true;
    return false;
}
function CanUserRegenerateThumbnail($user, $post) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    if ($user['GalleryPermissions'] == 'C') return true;
    return false;
}
function CanUserSearchUnlimitedClauses($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}
function CanUserMassTagEdit($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}
function CanUserSearchDeletedPosts($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}
function CanUserMassDeletePosts($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['GalleryPermissions'] == 'A') return true;
    return false;
}

// General path functions.
function GetSiteImagePath($md5, $ext) { return "/".GetImagePath($md5, $ext); }
function GetSystemImagePath($md5, $ext) { return SITE_ROOT.GetImagePath($md5, $ext); }

function GetSiteThumbPath($md5, $ext) { return "/".GetThumbPath($md5, $ext); }
function GetSystemThumbPath($md5, $ext) { return SITE_ROOT.GetThumbPath($md5, $ext); }

function GetSitePreviewPath($md5, $ext) { return "/".GetPreviewPath($md5, $ext); }
function GetSystemPreviewPath($md5, $ext) { return SITE_ROOT.GetPreviewPath($md5, $ext); }

function RawToSanitizedPoolName($raw, $accept_only_valid_lengths = false) {
    // Strip out _ so that this can be searched for.
    $result = str_replace("_", " ", $raw);
    // And strip out duplicate spaces.
    $result = mb_ereg_replace("\s+", " ", $result);
    $result = mb_substr($result, 0, MAX_GALLERY_POOL_NAME_LENGTH);
    if ($accept_only_valid_lengths && mb_strlen($result) < MIN_GALLERY_POOL_NAME_LENGTH) {
        return FALSE;
    }
    $result = GetSanitizedTextTruncated($result, NO_HTML_TAGS, MAX_GALLERY_POOL_NAME_LENGTH);
    return $result;
}
function SanitizedToRawPoolName($sanitized) {
    return htmlspecialchars_decode($sanitized);
}

// Gets a parent post id for a given post. If the parent doesn't exist, returns -1.
// Replaces "none" with -1. Replaces self-parenting with -1.
function GetValidParentPostId($parent_post_id, $post_id) {
    if (mb_strtolower($parent_post_id, "UTF-8") == "none") return -1;
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

// Updates a post with the new tags/properties (and updates history log).
function UpdatePost($tag_string, $post_id, $user, $update_tag_types=true, $batch_id=0) {
    global $GALLERY_TAG_TYPES;
    $old_tag_ids = GetPostTags($post_id);  // Get tags before post was edited.
    $tag_string = CleanTagString($tag_string);
    $tokens = GetTagStringTokens($tag_string);
    $descriptors = GetTagDescriptors($tokens, $post_id, "GalleryTagDescriptorFilterFn");
    UpdatePostWithDescriptors($descriptors, $post_id, $user, $batch_id);
    if ($update_tag_types) {
        UpdateTagTypes(GALLERY_TAG_TABLE, $GALLERY_TAG_TYPES, $descriptors, $user);  // Do after creating tags above when setting post tags.
    }
    $tag_ids = GetPostTags($post_id);  // Get tags after post was edited.
    $tags_added = array_diff($tag_ids, $old_tag_ids);
    $tags_removed = array_diff($old_tag_ids, $tag_ids);
    IncrementTagCounts(GALLERY_TAG_TABLE, $tags_added, /*increment=*/1);
    IncrementTagCounts(GALLERY_TAG_TABLE, $tags_removed, /*increment=*/-1);
}

function GetPostTags($pid) {
    $tag_ids = array();
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$pid;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $tag_ids[] = $row['TagId'];
        }
    }
    return $tag_ids;
}

// Updates a post with the new description (and updates history log).
function UpdatePostDescription($post_id, $description, $user, $log_change=true) {
    $escaped_description = sql_escape(GetSanitizedTextTruncated($description, NO_HTML_TAGS, MAX_GALLERY_POST_DESCRIPTION_LENGTH));
    $now = time();
    $user_id = $user['UserId'];
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET Description='$escaped_description' WHERE PostId=$post_id;");
    sql_query("INSERT INTO ".GALLERY_DESC_HISTORY_TABLE."
        (PostId, Timestamp, UserId, Description)
        VALUES
        ($post_id, $now, $user_id, '$escaped_description');");
}

// Writes post statistics to database.
function UpdatePostStatistics($post_id) {
    // NumFavorites
    if (sql_query_into($result, "SELECT count(*) FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE PostId=$post_id;", 1)) {
        $num_favorites = $result->fetch_assoc()['count(*)'];
    } else {
        $num_favorites = 0;
    }
    // NumComments
    if (sql_query_into($result, "SELECT count(*) FROM ".GALLERY_COMMENT_TABLE." WHERE PostId=$post_id;", 1)) {
        $num_comments = $result->fetch_assoc()['count(*)'];
    } else {
        $num_comments = 0;
    }

    // Update all fields.
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET NumFavorites=$num_favorites, NumComments=$num_comments WHERE PostId=$post_id;");
}



function UpdatePostWithDescriptors($descriptors, $post_id, $user, $batch_id=0) {
    $tag_descriptors = array_filter($descriptors, function($desc) { return $desc->isTag; });
    $tag_names = array_map(function($desc) { return $desc->tag; }, $tag_descriptors);
    $properties = array_filter($descriptors, function($desc) { return !$desc->isTag; });
    $log_fields = array();

    // Update tags.
    $tags = GetTagsByNameWithAliasAndImplied(GALLERY_TAG_TABLE, GALLERY_TAG_ALIAS_TABLE, GALLERY_TAG_IMPLICATION_TABLE, $tag_names, CanUserCreateGalleryTags($user), $user['UserId']);
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
        $tags = $tags + GetTagsById(GALLERY_TAG_TABLE, $tags_to_remove);
        $error = false;
        $tags_changed = false;
        $tags_to_remove = array_filter($tags_to_remove, function($tag_id) use ($tags) { return !$tags[$tag_id]['AddLocked']; });
        $tags_to_add = array_filter($tags_to_add , function($tag_id) use ($tags) { return !$tags[$tag_id]['AddLocked']; });
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
    if (sizeof($properties) > 0) {
        $escaped_post_id = sql_escape($post_id);
        if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1)) {
            $post = $result->fetch_assoc();
            $temp_log_lines = array();
            $sql_sets = array();
            foreach ($properties as $prop) {
                switch ($prop->label) {
                    // NOTE: When adding more here, make sure reverts take them into account.
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
        sql_query("INSERT INTO ".GALLERY_POST_TAG_HISTORY_TABLE." (PostId, Timestamp, UserId, BatchId, $keys) VALUES ($post_id, $now, $user_id, $batch_id, $values);");
    }
}

function PropertyDescriptorToLogEntry($descriptor) {
    return str_replace("parent:-1", "parent:none", $descriptor->label.":".$descriptor->tag);
}

function GalleryTagDescriptorFilterFn($token, $label, $tag, $post_id) {
    $obj = new stdClass();
    switch ($label) {
        case "rating":
            $obj->label = $label;
            $tagletter = mb_strtolower(mb_substr($tag, 0, 1), "UTF-8");
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
            $obj->tag = mb_strtolower($tag, "UTF-8");
            $obj->isTag = true;
            break;
        default:
            $obj->label = "";
            $obj->tag = mb_strtolower($token, "UTF-8");
            $obj->isTag = true;
            break;
    }
    return $obj;
}

// Creates label that floats under a post thumbnail.
function CreatePostLabel(&$post) {
    $post['favHtml'] = "<span>&hearts;".$post['NumFavorites']."</span>";
    $post['commentsHtml'] = "<span>C".$post['NumComments']."</span>";
    switch($post['Rating']) {
      case "s":
        $post['ratingHtml'] = "<span class='srating'>S</span>";
        break;
      case "q":
        $post['ratingHtml'] = "<span class='qrating'>Q</span>";
        break;
      case "e":
        $post['ratingHtml'] = "<span class='erating'>E</span>";
        break;
    }
}

// Create outline colors for post borders.
function SetOutlineClasses(&$posts) {
    $postsToCheckChild = array();
    foreach ($posts as &$post) {
        if ($post['Status'] == "P") {
            $post['outlineClass'] = "pendingoutline";
            continue;
        } else if ($post['Status'] == "F") {
            $post['outlineClass'] = "flaggedoutline";
            continue;
        }
        if ($post['ParentPostId'] != -1) {
            // Is a child.
            $post['outlineClass'] = "childoutline";
            continue;
        } else {
            $postsToCheckChild[$post['PostId']] = &$post;
            continue;
        }
    }
    $ids = array_unique(array_keys($postsToCheckChild));
    if (sizeof($ids) == 0) return;
    $joined = implode(",", $ids);
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPostId IN ($joined);", 0) or RenderErrorPage("No posts found");
    while ($row = $result->fetch_assoc()) {
        if ($row['Status'] != 'D') {  // Only set parents if the child isn't deleted.
            $postsToCheckChild[$row['ParentPostId']]['outlineClass'] = "parentoutline";
        }
    }
}

function GetBaseGalleryUploadLimit($user) {
    if ($user['GalleryPermissions'] == 'A') return 1000;
    if ($user['GalleryPermissions'] == 'C') return 10;
    if ($user['GalleryPermissions'] == 'R') return 0;
    if ($user['JoinTime'] + ALLOW_GALLERY_EDITS_AFTER_REGISTRATION_DEADLINE > time()) return 0;
    return 10;
}

function FetchUploadCountsByUserBySuccess($user, &$numPending, &$numSuccessful, &$numDeletedNotFlaggedBySelf) {
    $numPending = 0;
    $numSuccessful = 0;
    $numDeletedNotFlaggedBySelf = 0;
    $uid = $user['UserId'];
    if (!sql_query_into($result, "SELECT
        count(CASE Status WHEN 'P' THEN 1 ELSE NULL END) AS NumPending,
        count(CASE Status WHEN 'A' THEN 1 ELSE NULL END) AS NumSuccess,
        count(CASE Status WHEN 'D' THEN (CASE FlaggerUserId WHEN $uid THEN NULL ELSE 1 END) ELSE NULL END) AS NumFail
        FROM ".GALLERY_POST_TABLE." WHERE UploaderId=$uid;", 1)) return false;
    $row = $result->fetch_assoc();
    $numPending = $row['NumPending'];
    $numSuccessful = $row['NumSuccess'];
    $numDeletedNotFlaggedBySelf = $row['NumFail'];
    return true;
}

function ComputeUploadLimit($user, $numSuccessful, $numDeletedNotFlaggedBySelf) {
    $base_limit = GetBaseGalleryUploadLimit($user);
    return (int)round($base_limit + ($numSuccessful / 10.0) - ($numDeletedNotFlaggedBySelf/4.0));
}

function CanUserUpload($user, $numPending, $numSuccessful, $numDeletedNotFlaggedBySelf) {
    $limit = ComputeUploadLimit($user, $numSuccessful, $numDeletedNotFlaggedBySelf);
    return $numPending < $limit;
}
function QuickCanUserUpload($user) {
    return FetchUploadCountsByUserBySuccess($user, $numPending, $numSuccessful, $numDeletedNotFlaggedBySelf) && CanUserUpload($user, $numPending, $numSuccessful, $numDeletedNotFlaggedBySelf);
}

function GetTags($pid, $fresh=false) {
    static $cache = array();
    $allTagIds = array();
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$pid;", 0)) return null;
    while ($row = $result->fetch_assoc()) {
        $allTagIds[] = $row['TagId'];
    }

    $ret = array();
    if (!$fresh) {
        $origSize = sizeof($allTagIds);
        for ($i = 0; $i < $origSize; $i++) {
            if (isset($cache[$allTagIds[$i]])) {
                $ret[$allTagIds[$i]] = $cache[$allTagIds[$i]];
                unset($allTagIds[$i]);
            }
        }
    }
    $result = GetTagsById(GALLERY_TAG_TABLE, $allTagIds);
    foreach ($result as $key => $value) {
        $cache[$key] = $value;
    }
    $ret = $ret + $result;
    return $ret;
}

function ToTagNameString($allTags) {
    $tagNames = array_map(function($tag) { return $tag['Name']; }, $allTags);
    sort($tagNames);
    return implode(" ", $tagNames);
}

function HasAnimatedTag($allTags) {
    foreach ($allTags as $tag) {
        if (mb_strtolower($tag['Name']) == 'animated') {
            return true;
        }
    }
    return false;
}

function GetPossibleFlagReasons() {
    return GetSiteSettingArray("gallery_flag_reasons");
}

?>