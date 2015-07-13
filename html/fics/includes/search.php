<?php
// Included php file for handling searches in the fics section.

include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");
include_once(SITE_ROOT."fics/includes/functions.php");

function GetSearchClauses($search_term_string) {
    $search_term_array = explode(" ", $search_term_string);
    $search_term_array = array_map("trim", $search_term_array);
    $search_term_array = array_filter($search_term_array, "mb_strlen");
    $search_term_array = array_slice($search_term_array, 0, MAX_FICS_SEARCH_TERMS);
    $search_term_array = array_merge($search_term_array, GetFicsBlacklistClauses($search_term_array));
    $clauses = array_filter(array_map("GetClause", $search_term_array), "mb_strlen");
    $clause_string = implode(" AND ", array_map(function($clause) { return "($clause)"; }, $clauses));
    // If no status:, omit pending and deleted by default.
    if (!contains($clause_string, "ApprovalStatus")) {
        $clauses[] = "(ApprovalStatus<>'P' AND ApprovalStatus<>'D')";
        $clause_string = implode(" AND ", array_map(function($clause) { return "($clause)"; }, $clauses));
    }
    return $clause_string;
}

function GetOrderingClauses($search_term_string) {
    $search_term_array = explode(" ", $search_term_string);
    $search_term_array = array_map("trim", $search_term_array);
    $search_term_array = array_filter($search_term_array, "mb_strlen");
    $search_term_array = array_slice($search_term_array, 0, MAX_FICS_SEARCH_TERMS);
    $orderings = array_filter(array_map("GetOrdering", $search_term_array), "mb_strlen");
    return implode(", ", $orderings);
}

function GetOrdering($search_term) {
    $search_term = mb_strtolower($search_term);
    if (startsWith($search_term, "order:rating") ||
        startsWith($search_term, "order:score")) {
        return "(TotalStars / IF (TotalRatings = 0, 1, TotalRatings)) DESC";
    } else if (startsWith($search_term, "order:views") ||
        startsWith($search_term, "order:reads")) {
        return "Views DESC";
    } else if (startsWith($search_term, "order:words") ||
        startsWith($search_term, "order:length") ||
        startsWith($search_term, "order:size")) {
        return "WordCount DESC";
    } else if (startsWith($search_term, "order:chapters")) {
        return "ChapterCount DESC";
    } else if (startsWith($search_term, "order:reviews")) {
        return "NumReviews DESC";
    } else if (startsWith($search_term, "order:published")) {
        return "DateCreated DESC";
    } else {
        return "";
    }
}

function GetFicsBlacklistClauses($terms) {
    global $user;
    if (!isset($user)) return array();
    $blacklist_terms = explode(" ", $user['FicsTagBlacklist']);
    $blacklist_terms = array_filter($blacklist_terms, "mb_strlen");
    $blacklist_terms = array_slice($blacklist_terms, 0, MAX_FICS_BLACKLIST_TAGS);
    $blacklist_terms = array_filter($blacklist_terms, function($term) use ($terms) {
        return !in_array($term, $terms);
    });
    $blacklist_terms = array_map(function($term) { return "-$term"; }, $blacklist_terms);
    return $blacklist_terms;
}

function GetClause($search_term) {
    global $user;
    $order = GetOrdering($search_term);
    if (mb_strlen($order) > 0) {
        return "";
    }
    if (mb_substr($search_term, 0, 1) == "-") {
        while (mb_substr($search_term, 0, 1) == "-") {
            $search_term = mb_substr($search_term, 1);
        }
        return "NOT(".GetClause($search_term).")";
    }
    if (mb_strlen($search_term) == 0) return "";
    $lower_term = mb_strtolower($search_term);
    if ($lower_term == "completed" ||
        $lower_term == "complete" ||
        $lower_term == "completed:yes" ||
        $lower_term == "completed:true") return "Completed=TRUE";
    if ($lower_term == "not_completed" ||
        $lower_term == "incomplete" ||
        $lower_term == "completed:no" ||
        $lower_term == "completed:false") return "Completed=FALSE";
    if ($lower_term == "rating:g") return "Rating='G'";
    if ($lower_term == "rating:pg") return "Rating='P'";
    if ($lower_term == "rating:pg-13") return "Rating='T'";
    if ($lower_term == "rating:r") return "Rating='R'";
    if ($lower_term == "rating:xxx") return "Rating='X'";
    if ($lower_term == "featured" ||
        $lower_term == "is_featured" ||
        $lower_term == "featured:yes" ||
        $lower_term == "featured:true") return "Featured<>'".FICS_NOT_FEATURED."'";
    if ($lower_term == "not_featured" ||
        $lower_term == "featured:no" ||
        $lower_term == "featured:false") return "Featured='".FICS_NOT_FEATURED."'";
    if (startsWith($lower_term, "status:p")) {
        return "ApprovalStatus='P'";
    } else if (startsWith($lower_term, "status:a")) {
        return "ApprovalStatus='A'";
    } else if (startsWith($lower_term, "status:d")) {
        // Don't allow searches for deleted fics.
        if (isset($user) && CanUserSearchDeletedStories($user)) {
            return "ApprovalStatus='D'";
        }
    }
    // Strip "", if it exists. No multi-byte needed.
    // Allows for searching for terms that are also filters.
    $search_term = str_replace("\"", "", $search_term);

    $tag = ClauseForTag($search_term);
    $title = ClauseForTitle($search_term);
    $author = ClauseForAuthor($search_term);
    $summary = ClauseForSummary($search_term);
    $ret = "";
    if ($tag != null) $ret .= " OR ($tag)";
    if ($title != null) $ret .= " OR ($title)";
    if ($author != null) $ret .= " OR ($author)";
    if ($summary != null) $ret .= " OR ($summary)";
    if (mb_strlen($ret) > 0) {
        // Starts with " OR ", remove this prefix.
        $ret = mb_substr($ret, 4);
    }
    return $ret;
}

function ClauseForTag($tag_name) {
    $tag_name = SanitizeTagName($tag_name);  // Remove extra punctuation.
    $tags = GetTagsByNameWithAliasAndImplied(FICS_TAG_TABLE, FICS_TAG_ALIAS_TABLE, FICS_TAG_IMPLICATION_TABLE, array($tag_name), false, -1, true, false, false);  // Apply alias, but don't drop tags.
    if ($tags == null || sizeof($tags) == 0) return null;
    $tag_ids = array_keys($tags);
    $joined = implode(",", $tag_ids);
    return "EXISTS(SELECT 1 FROM ".FICS_STORY_TAG_TABLE." U WHERE T.StoryId=U.StoryId AND U.TagId IN ($joined) LIMIT 1)";
}

function ClauseForTitle($text) {
    // Replace _ with space, if user put it in.
    $title = mb_strtolower(mb_ereg_replace("_+", " ", $text));
    if (mb_strlen($title) < MIN_FICS_TITLE_SUMMARY_SEARCH_STRING_SIZE) return "";
    $escaped_title = sql_escape($title);
    return "LOWER(Title) LIKE '%$escaped_title%'";
}

function ClauseForSummary($text) {
    // Replace _ with space, if user put it in.
    $summary = mb_strtolower(mb_ereg_replace("_+", " ", $text));
    if (mb_strlen($summary) < MIN_FICS_TITLE_SUMMARY_SEARCH_STRING_SIZE) return "";
    $escaped_summary = sql_escape($summary);
    return "LOWER(Summary) LIKE '%$escaped_summary%'";
}

function ClauseForAuthor($author_display_name) {
    // Replace _ with space, if user put it in.
    $display_name = mb_strtolower(mb_ereg_replace("_+", " ", $author_display_name));
    $escaped_display_name = sql_escape($display_name);
    if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE LOWER(DisplayName) LIKE '%$escaped_display_name%';", 1)) return null;
    $user_ids = array();
    while ($row = $result->fetch_assoc()) {
        $user_ids[] = $row['UserId'];
    }
    if (sizeof($user_ids) == 0) return null;  // Should never happen, but just in case.
    $joined_ids = implode(",", $user_ids);
    return "AuthorUserId IN ($joined_ids)";
}
?>