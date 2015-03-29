<?php
// Class for specifying a search tree.
// Search terms can include the following modifiers:
// {tag}
// ~{tag}
// -{clause}
// rating:{s,q,e} (first letter considered only)
// user:{uploader-display-name}
// fav:{user-display-name}
// id:{PostId}
// md5:{Md5}
// parent:{PostId}
// status:{deleted/flagged/pending/none} (If omitted, defaults to -status:deleted)
// pool:{PoolId}
//
// === Not implemented yet ===
// order:
// score:
// comments:

function CreateSQLClauses($search) {
    return CreateSQLClausesFromTerms(array_slice(explode(" ", $search), 0, MAX_GALLERY_SEARCH_TERMS));
}

function CreateSQLClausesFromTerms($terms, $mode="AND") {
    $or_clauses = array();
    $and_clauses = array();
    $filter_clauses = array();
    if ($terms != array("")) {
        foreach ($terms as $term) {
            if (startsWith($term, "~")) {
                $or_clauses[] = substr($term, 1);
            } else if (strpos($term, ":") !== FALSE) {
                $filter_clauses[] = $term;
            } else {
                $and_clauses[] = $term;
            }
        }
    }
    $sql = array();
    if (sizeof($or_clauses) > 0) {
        $sql[] = "(".CreateSQLClausesFromTerms($or_clauses, "OR").")";
    }
    if (sizeof($and_clauses) > 0) {
        foreach ($and_clauses as $term) {
            $sql[] = "(".CreateSQLClauseFromTerm($term).")";
        }
    }
    if (sizeof(array_filter($filter_clauses, function($str) {
        return preg_match("/-*status:deleted/", $str) == 1;
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
        return "NOT(".CreateSQLClauseFromTerm(substr($term, 1)).")";
    } else {
        return "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." PT JOIN ".GALLERY_TAG_TABLE." TG ON PT.TagId=TG.TagId WHERE T.PostId=PostId AND TG.Name='".sql_escape($term)."')";
    }
}

function CreateSQLClauseFromFilter($filter) {
    if (startsWith($filter, "-")) {
        return "NOT(".CreateSQLClauseFromFilter(substr($filter, 1)).")";
    } else {
        if (startsWith($filter, "id:")) {
            $id = substr($filter, 3);
            $escaped_id = sql_escape($id);
            return "T.PostId='$escaped_id'";
        } else if (startsWith($filter, "md5:")) {
            $md5 = substr($filter, 4);
            $escaped_md5 = sql_escape($md5);
            return "T.Md5='$escaped_md5'";
        } else if (startsWith($filter, "rating:")) {
            $rating = substr($filter, 7);
            $escaped_rating = sql_escape(substr($rating, 0, 1));
            return "T.Rating='$escaped_rating'";
        } else if (startsWith($filter, "user:")) {
            $name = substr($filter, 5);
            $escaped_name = sql_escape($name);
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U WHERE U.DisplayName='$escaped_name' AND T.UploaderId=U.UserId)";
        } else if (startsWith($filter, "fav:")) {
            $name = substr($filter, 4);
            $escaped_name = sql_escape($name);
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U JOIN ".GALLERY_USER_FAVORITES_TABLE." F ON U.UserId=F.UserId WHERE U.DisplayName='$escaped_name' AND T.PostId=F.PostId)";
        } else if (startsWith($filter, "parent:")) {
            $parent = substr($filter, 7);
            if (strtolower($parent) == "none" || !is_numeric($parent) || $parent <= "0") return "FALSE";  // Don't let searching for all non-child posts.
            $escaped_parent = sql_escape($escaped_parent);
            return "T.ParentPostId='$escaped_parent'";
        } else if (startsWith($filter, "status:")) {
            $status = substr($filter, 7);
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
            $pool = substr($filter, 5);
            $escaped_pool = sql_escape($pool);
            return "EXISTS(SELECT 1 FROM ".GALLERY_POOL_MAP_TABLE." U WHERE U.PoolId='$escaped_pool' AND T.PostId=U.PostId)";
        } else {
            return "FALSE";
        }
    }
}
?>