<?php
// Included php file for handling searches in the fics section.

include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

function GetSearchClauses($search_term_array) {
    if (sizeof($search_term_array) > MAX_FICS_SEARCH_TERMS) $search_term_array = array_slice($search_term_array, 0, MAX_FICS_SEARCH_TERMS);
    $clauses = array_filter(array_map("GetClause", $search_term_array), "mb_strlen");
    return implode(" AND ", array_map(function($clause) { return "($clause)"; }, $clauses));
}

function GetClause($search_term) {
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