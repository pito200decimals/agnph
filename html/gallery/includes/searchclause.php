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
            $escaped_id = sql_escape(substr($filter, 3));
            return "T.PostId='$escaped_id'";
        } else if (startsWith($filter, "md5:")) {
            $escaped_md5 = sql_escape(substr($filter, 4));
            return "T.Md5='$escaped_md5'";
        } else if (startsWith($filter, "rating:")) {
            $rating = substr($filter, 7);
            $escaped_rating = sql_escape(substr($rating, 0, 1));
            return "T.Rating='$escaped_rating'";
        } else if (startsWith($filter, "user:")) {
            $name = substr($filter, 5);
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U WHERE U.DisplayName='$name' AND T.UploaderId=U.UserId)";
        } else if (startsWith($filter, "fav:")) {
            $name = substr($filter, 4);
            return "EXISTS(SELECT 1 FROM ".USER_TABLE." U JOIN ".GALLERY_USER_FAVORITES_TABLE." F ON U.UserId=F.UserId WHERE U.DisplayName='$name' AND T.PostId=F.PostId)";
        } else if (startsWith($filter, "parent:")) {
            $rating = substr($filter, 7);
            if ($rating == "-1") return "FALSE";  // Don't let searching for all non-child posts.
            $escaped_rating = sql_escape(substr($rating, 0, 1));
            return "T.ParentPostId='$escaped_rating'";
        } else if (startsWith($filter, "status:")) {
            $rating = substr($filter, 7);
            $escaped_rating = sql_escape(substr($rating, 0, 1));
            if ($rating == "none") {
                return "T.Status='A'";
            } else if ($rating == "pending") {
                return "T.Status='P'";
            } else if ($rating == "flagged") {
                return "T.Status='F'";
            } else if ($rating == "deleted") {
                return "T.Status='D'";
            } else {
                return "FALSE";
            }
            return "ParentPostId='$escaped_rating'";
        } else {
            return "FALSE";
        }
    }
}
?>