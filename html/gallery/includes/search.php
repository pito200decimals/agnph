<?php
// General functions related to searching for posts.

include_once(SITE_ROOT."gallery/includes/searchclause.php");

// Search orderings implemented:
// order:score
// order:date
// order:age
//
// === Not implemented yet ===
// order:favorites
// order:date
function CreatePostSearchSQL($search_string, $posts_per_page, $page, &$can_sort_pool, &$pool_sort_id) {
    $offset = ($page - 1) * $posts_per_page;
    $page_size = $posts_per_page;
    $sortOrder = "T.PostId DESC";
    $can_sort_pool = false;
    $matches = array();
    // Safe to use preg_match here.
    if (preg_match("/^.*pool:(\d+)($|[^\d].*$)/i", $search_string, $matches)) {
        // Pool search, use this as default (with old default as tiebreaker).
        $sortOrder = "T.PoolItemOrder ASC, ".$sortOrder;
        $can_sort_pool = true;
        $pool_sort_id = $matches[1];
    }
    // If order specified, use new order.
    if (mb_strpos(mb_strtolower($search_string), "order:") !== FALSE) {
        // Check for various orderings (with this priority lowest to highest).
        if (mb_strpos(mb_strtolower($search_string), "order:date") !== FALSE) {
            $search_string = mb_eregi_replace("order:date", "", $search_string);
            $sortOrder = "T.DateUploaded DESC, ".$sortOrder;
            $can_sort_pool = false;
        }
        if (mb_strpos(mb_strtolower($search_string), "order:age") !== FALSE) {
            $search_string = mb_eregi_replace("order:age", "", $search_string);
            $sortOrder = "T.DateUploaded ASC, ".$sortOrder;
            $can_sort_pool = false;
        }
        if (mb_strpos(mb_strtolower($search_string), "order:score") !== FALSE) {
            $search_string = mb_eregi_replace("order:score", "", $search_string);
            $sortOrder = "T.Score DESC, ".$sortOrder;
            $can_sort_pool = false;
        }
        if (mb_strpos(mb_strtolower($search_string), "order:views") !== FALSE) {
            $search_string = mb_eregi_replace("order:views", "", $search_string);
            $sortOrder = "T.NumViews DESC, ".$sortOrder;
            $can_sort_pool = false;
        }
    }
    if ($can_sort_pool) {
        $offset = 0;
        $page_size = 250;  // Max sort space for a pool.
    }
    return "SELECT * FROM ".GALLERY_POST_TABLE." T WHERE ".CreateSQLClauses($search_string)." ORDER BY $sortOrder LIMIT $posts_per_page OFFSET $offset;";
}

// Returns the number of posts in the given query, or -1 if a failure occurs.
function CountNumPosts($search_string) {
    $sql = "SELECT count(*) FROM ".GALLERY_POST_TABLE." T WHERE ".CreateSQLClauses($search_string).";";
    if (!sql_query_into($result, $sql, 1)) return -1;  // Get 1 row containing the count.
    $row = $result->fetch_assoc();
    return $row['count(*)'];
}
?>