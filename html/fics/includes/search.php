<?php
// Included php file for handling searches in the fics section.

include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

// TODO: Ordering.

function GetSearchClauses($search_term_string) {
    $search_term_array = explode(" ", $search_term_string);
    $search_term_array = array_map("trim", $search_term_array);
    $search_term_array = array_filter($search_term_array, "mb_strlen");
    $search_term_array = array_slice($search_term_array, 0, MAX_FICS_SEARCH_TERMS);
    $search_term_array = array_merge($search_term_array, GetBlacklistClauses($search_term_array));
    $clauses = array_filter(array_map("GetClause", $search_term_array), "mb_strlen");
    return implode(" AND ", array_map(function($clause) { return "($clause)"; }, $clauses));
}

function GetBlacklistClauses($terms) {
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
    if (mb_substr($search_term, 0, 1) == "-") {
        while (mb_substr($search_term, 0, 1) == "-") {
            $search_term = mb_substr($search_term, 1);
        }
        return "NOT(".GetClause($search_term).")";
    }
    if (mb_strlen($search_term) == 0) return "";
    if (mb_strtolower($search_term) == "completed" ||
        mb_strtolower($search_term) == "complete" ||
        mb_strtolower($search_term) == "completed:yes" ||
        mb_strtolower($search_term) == "completed:true") return "Completed=TRUE";
    if (mb_strtolower($search_term) == "not_completed" ||
        mb_strtolower($search_term) == "incomplete" ||
        mb_strtolower($search_term) == "completed:no" ||
        mb_strtolower($search_term) == "completed:false") return "Completed=FALSE";
    if (mb_strtolower($search_term) == "rating:g") return "Rating='G'";
    if (mb_strtolower($search_term) == "rating:pg") return "Rating='P'";
    if (mb_strtolower($search_term) == "rating:pg-13") return "Rating='T'";
    if (mb_strtolower($search_term) == "rating:r") return "Rating='R'";
    if (mb_strtolower($search_term) == "rating:xxx") return "Rating='X'";
    if (mb_strtolower($search_term) == "featured" ||
        mb_strtolower($search_term) == "featured:yes" ||
        mb_strtolower($search_term) == "featured:true") return "Featured<>'N'";
    if (mb_strtolower($search_term) == "not_featured" ||
        mb_strtolower($search_term) == "featured:no" ||
        mb_strtolower($search_term) == "featured:false") return "Featured='N'";
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
    $tag = GetTagsByName(FICS_TAG_TABLE, array($tag_name));  // No automatic creation.
    if ($tag == null || sizeof($tag) == 0) return null;
    $tag = array_values($tag)[0];
    $tag_id = $tag['TagId'];
    return "EXISTS(SELECT 1 FROM ".FICS_STORY_TAG_TABLE." U WHERE T.StoryId=U.StoryId AND U.TagId=$tag_id LIMIT 1)";
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