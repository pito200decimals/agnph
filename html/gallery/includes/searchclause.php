<?php
// Class for specifying a search tree.
// Search terms can include the following modifiers:
// {tag}            (and tag)
// ~{tag}           (or tag)
// -{clause}        (and not tag)
// rating:{s,q,e} (first letter considered only)
// user:{uploader-display-name}
// fav:{user-display-name}
// id:{PostId}
// md5:{Md5}
// parent:{PostId}
// status:{deleted/flagged/pending/none} (If omitted, defaults to -status:deleted)
// pool:{PoolId}
// file:{jpg, webm, etc}
//
// === Not implemented yet ===
// comments:
// width/height/aspect-ratio:
// Has artist, etc?

function CreateSQLClauses($search) {
    $terms = explode(" ", $search);
    $terms = array_map("trim", $terms);
    $terms = array_filter($terms, "mb_strlen");
    $terms = array_slice($terms, 0, MAX_GALLERY_SEARCH_TERMS);
    return CreateSQLClausesFromTerms($terms);
}

function CreateSQLClausesFromTerms($terms, $mode="AND") {
    $or_terms = array();
    $and_terms = array();
    $filter_clauses = array();
    if ($terms != array("")) {
        foreach ($terms as $term) {
            if (startsWith($term, "~")) {
                $or_terms[] = mb_substr($term, 1);
            } else if (mb_strpos($term, ":") !== FALSE) {
                $filter_clauses[] = $term;
            } else {
                $and_terms[] = $term;
            }
        }
    }
    $and_terms = array_merge($and_terms, GetGalleryBlacklistClauses($and_terms, $or_terms));
    $sql = array();
    if (sizeof($or_terms) > 0) {
        $sql[] = "(".CreateSQLClausesFromTerms($or_terms, "OR").")";
    }
    if (sizeof($and_terms) > 0) {
        foreach ($and_terms as $term) {
            $sql[] = "(".CreateSQLClauseFromTerm($term).")";
        }
    }
    if ($mode == "AND" &&
        sizeof(array_filter($filter_clauses, function($str) {
        return mb_ereg_match("-*status:deleted", $str) == 1;
    })) == 0) {
        $filter_clauses[] = "-status:deleted";
    }
    if (sizeof($filter_clauses) > 0) {
        foreach($filter_clauses as $term) {
            $sql[] = "(".CreateSQLClauseFromFilter($term).")";
        }
    }
    return implode(" $mode ", $sql);
}

function CreateSQLClauseFromTerm($term) {
    if (startsWith($term, "-")) {
        return "NOT(".CreateSQLClauseFromTerm(mb_substr($term, 1)).")";
    } else {
        // Get appropriate tag id.
        $tags = GetTagsByNameWithAliasAndImplied(GALLERY_TAG_TABLE, GALLERY_TAG_ALIAS_TABLE, GALLERY_TAG_IMPLICATION_TABLE, array($term), false, -1, true, false, false);  // Apply alias, but don't drop tags.
        $tag_ids = array_keys($tags);
        $joined = implode(",", $tag_ids);
        return "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." WHERE T.PostId=PostId AND TagId IN ($joined) LIMIT 1)";
    }
}

function CreateSQLClauseFromFilter($filter) {
    if (startsWith($filter, "-")) {
        return "NOT(".CreateSQLClauseFromFilter(mb_substr($filter, 1)).")";
    } else {
        if (startsWith($filter, "id:")) {
            $id = mb_substr($filter, 3);
            $escaped_id = sql_escape($id);
            return "T.PostId='$escaped_id'";
        } else if (startsWith($filter, "md5:")) {
            $md5 = mb_substr($filter, 4);
            $escaped_md5 = sql_escape($md5);
            return "T.Md5='$escaped_md5'";
        } else if (startsWith($filter, "rating:")) {
            $rating = mb_substr($filter, 7);
            $escaped_rating = sql_escape(mb_substr($rating, 0, 1));
            return "T.Rating='$escaped_rating'";
        } else if (startsWith($filter, "user:")) {
            $name = mb_substr($filter, 5);
            $escaped_name = sql_escape($name);
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U WHERE U.DisplayName='$escaped_name' AND T.UploaderId=U.UserId)";
        } else if (startsWith($filter, "fav:")) {
            $name = mb_substr($filter, 4);
            $escaped_name = sql_escape($name);
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U JOIN ".GALLERY_USER_FAVORITES_TABLE." F ON U.UserId=F.UserId WHERE U.DisplayName='$escaped_name' AND T.PostId=F.PostId)";
        } else if (startsWith($filter, "parent:")) {
            $parent = mb_substr($filter, 7);
            if (mb_strtolower($parent) == "none" || !is_numeric($parent) || $parent <= 0) return "FALSE";  // Don't let searching for all non-child posts.
            $escaped_parent = sql_escape($parent);
            return "T.ParentPostId='$escaped_parent'";
        } else if (startsWith($filter, "status:")) {
            $status = mb_substr($filter, 7);
            if ($status == "none") {
                return "T.Status='A'";
            } else if ($status == "pending") {
                return "T.Status='P'";
            } else if ($status == "flagged") {
                return "T.Status='F'";
            } else if ($status == "deleted") {
                return "T.Status='D'";
            } else {
                return "FALSE";
            }
        } else if (startsWith($filter, "pool:")) {
            $pool = mb_substr($filter, 5);
            if (mb_strtolower($pool) == "none" || !is_numeric($pool) || $pool <= 0) return "FALSE";  // Don't let searching for all non-pool posts.
            $escaped_pool = sql_escape($pool);
            return "T.ParentPoolId='$escaped_pool'";
        } else if (startsWitH($filter, "file:")) {
            $file_type = mb_substr($filter, 5);
            $escaped_file_type = sql_escape($file_type);
            return "T.Extension='$escaped_file_type'";
        } else {
            // Fallback on normal search clauses.
            return CreateSQLClauseFromTerm($filter);
        }
    }
}

function GetGalleryBlacklistClauses($and_terms, $or_terms) {
    global $user;
    if (!isset($user)) return array();
    $terms = array_merge($and_terms, $or_terms);
    $blacklist_terms = explode(" ", $user['GalleryTagBlacklist']);
    $blacklist_terms = array_filter($blacklist_terms, "mb_strlen");
    $blacklist_terms = array_slice($blacklist_terms, 0, MAX_GALLERY_BLACKLIST_TAGS);
    $blacklist_terms = array_filter($blacklist_terms, function($term) use ($terms) {
        return !in_array($term, $terms);
    });
    $blacklist_terms = array_map(function($term) { return "-$term"; }, $blacklist_terms);
    return $blacklist_terms;
}
?>