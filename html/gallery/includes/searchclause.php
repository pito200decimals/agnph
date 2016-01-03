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
// source:
// missing_artist
//
// === Not implemented yet ===
// comments:
// width/height/aspect-ratio:

function CreateSQLClauses($search) {
    global $user;
    $terms = explode(" ", $search);
    $terms = array_map("trim", $terms);
    $terms = array_filter($terms, "mb_strlen");
    if (!isset($user) || !CanUserSearchUnlimitedClauses($user)) {
        $terms = array_slice($terms, 0, MAX_GALLERY_SEARCH_TERMS);
    }
    return CreateSQLClausesFromTerms($terms);
}

function CreateSQLClausesFromTerms($terms, $mode="AND") {
    global $user;
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
    if ($mode == "AND") {
        $and_terms = array_merge($and_terms, GetGalleryBlacklistClauses($and_terms, $or_terms));
    }
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
        !FilterHasClause($filter_clauses, "-*status:deleted")&&
        !FilterHasClause($or_terms, "-*status:deleted")) {
        $filter_clauses[] = "-status:deleted";
    }
    if ($mode == "AND" &&
        FilterHasClause($filter_clauses, "order:popular") &&
        !FilterHasClause($filter_clauses, "-*file:swf") &&
        !FilterHasClause($filter_clauses, "-*file:webm") &&
        !FilterHasClause($or_terms, "-*file:swf") &&
        !FilterHasClause($or_terms, "-*file:webm")) {
        $filter_clauses[] = "-file:swf";
        $filter_clauses[] = "-file:webm";
    }
    if (sizeof($filter_clauses) > 0) {
        foreach($filter_clauses as $term) {
            $sql[] = "(".CreateSQLClauseFromFilter($term).")";
        }
    }
    $sql = implode(" $mode ", $sql);
    if (isset($user) && CanUserSearchDeletedPosts($user)) {
        // Any sql is fine.
    } else {
        // Ensure all results are not deleted.
        $sql = "($sql) AND T.Status<>'D'";
    }
    return $sql;
}

function FilterHasClause($filter_clauses, $reg) {
    return sizeof(array_filter($filter_clauses, function($str) use ($reg) {
        return mb_ereg_match($reg, $str) == 1;
      })) == 1;
}

function CreateSQLClauseFromTerm($term) {
    if (startsWith($term, "-")) {
        return "NOT(".CreateSQLClauseFromTerm(mb_substr($term, 1)).")";
    } else if (mb_strtolower($term, "UTF-8") == "missing_artist") {
        return "NOT(EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." PT WHERE T.PostId=PT.PostId AND EXISTS(SELECT 1 FROM ".GALLERY_TAG_TABLE." TG WHERE TG.TagId=PT.TagId AND TG.Type='A')))";
    } else if (mb_strtolower($term, "UTF-8") == "missing_species") {
        return "NOT(EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." PT WHERE T.PostId=PT.PostId AND EXISTS(SELECT 1 FROM ".GALLERY_TAG_TABLE." TG WHERE TG.TagId=PT.TagId AND TG.Type='D')))";
    } else {
        // Get appropriate tag id.
        $tags = GetTagsByNameWithAliasAndImplied(GALLERY_TAG_TABLE, GALLERY_TAG_ALIAS_TABLE, GALLERY_TAG_IMPLICATION_TABLE, array($term), false, -1, true, false, false);  // Apply alias, but don't drop tags.
        $tag_ids = array_keys($tags);
        if (sizeof($tag_ids) == 0) return "FALSE";
        $joined = implode(",", $tag_ids);
        return "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." WHERE T.PostId=PostId AND TagId IN ($joined) LIMIT 1)";
    }
}

function CreateSQLClauseFromFilter($filter) {
    global $user;
    if (startsWith($filter, "-")) {
        return "NOT(".CreateSQLClauseFromFilter(mb_substr($filter, 1)).")";
    } else {
        $match = array();  // For regex matching.
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
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U WHERE UPPER(U.DisplayName)=UPPER('$escaped_name') AND T.UploaderId=U.UserId)";
        } else if (preg_match("/^fav(e|orite[ds]?)?:(.*)$/", $filter, $match)) {
            $name = $match[2];
            if (isset($user)) {
                $uid = $user['UserId'];
                if ($name == "me") return "EXISTS(SELECT 1 FROM ".GALLERY_USER_FAVORITES_TABLE." F WHERE UserId=$uid AND F.PostId=T.PostId)";
            } else {
                $uid = -1;
            }
            $escaped_name = sql_escape($name);
            $uids = array();
            // Get any users with name matching search, and either their settings allow visibility, or it's the self user.
            if (sql_query_into($result,
                "SELECT UserId FROM ".USER_TABLE." U WHERE
                LOWER(DisplayName) LIKE '%$escaped_name%' AND
                (UserId=$uid OR EXISTS(SELECT 1 FROM ".GALLERY_USER_PREF_TABLE." P WHERE P.UserId=U.UserId AND P.PrivateGalleryFavorites=0));", 1)) {
                while ($row = $result->fetch_assoc()) {
                    $uids[] = $row['UserId'];
                }
            }
            $joined_uids = implode(",", $uids);
            return "EXISTS(SELECT 1 FROM ".GALLERY_USER_FAVORITES_TABLE." F WHERE F.PostId=T.PostId AND F.UserId IN ($joined_uids))";
        } else if (startsWith($filter, "parent:")) {
            $parent = mb_substr($filter, 7);
            if (mb_strtolower($parent, "UTF-8") == "none" || !is_numeric($parent) || $parent <= 0) return "FALSE";  // Don't let searching for all non-child posts.
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
            if (mb_strtolower($pool, "UTF-8") == "none" || !is_numeric($pool) || $pool <= 0) return "FALSE";  // Don't let searching for all non-pool posts.
            $escaped_pool = sql_escape($pool);
            return "T.ParentPoolId='$escaped_pool'";
        } else if (startsWith($filter, "file:")) {
            $file_type = mb_substr($filter, 5);
            $escaped_file_type = sql_escape($file_type);
            return "T.Extension='$escaped_file_type'";
        } else if (startsWith($filter, "source:")) {
            $source = mb_substr($filter, 7);
            if (mb_strlen($source) > 0) {
                $escaped_source = sql_escape($source);
                return "UPPER(T.Source) LIKE UPPER('%$escaped_source%')";
            } else {
                return "FALSE";
            }
        } else if (startsWith($filter, "order:")) {
            return "TRUE";
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