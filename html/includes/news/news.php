<?php
// Utility functions for fetching news posts cross-section.

include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."forums/includes/functions.php");

function GetNewsPosts($boardName, $section = null) {
    if ($boardName != null && sql_query_into($result, "SELECT BoardId FROM ".FORUMS_BOARD_TABLE." WHERE UPPER(Name)=UPPER('".sql_escape($boardName)."');", 1)) {
        $news_board_id = $result->fetch_assoc()['BoardId'];
        $news = array();
        if (sql_query_into($result, "SELECT * FROM ".FORUMS_POST_TABLE." WHERE IsThread=1 AND ParentId=$news_board_id AND NewsPost=1 ORDER BY PostDate DESC;", 1)) {
            while ($row = $result->fetch_assoc()) {
                $row['date'] = FormatDate($row['PostDate'], NEWS_POST_DATE_FORMAT);
                $row['Text'] = SanitizeHTMLTags($row['Text'], DEFAULT_ALLOWED_TAGS);
                $row['section'] = $section;
                $news[] = $row;
            }
        }
        if (sizeof($news) > 0) {
            $news[0]['mobile'] = true;
        }
        InitPosters($news);
        return $news;
    }
    return null;
}

?>