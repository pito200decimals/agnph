<?php
// General functions related to searching for posts.

include_once(SITE_ROOT."gallery/includes/searchclause.php");

function CreatePostSearchSQL($search_string, $posts_per_page, $page) {
    $offset = ($page - 1) * $posts_per_page;
    return "SELECT * FROM ".GALLERY_POST_TABLE." T WHERE ".CreateSQLClauses($search_string)." ORDER BY PostId DESC LIMIT $posts_per_page OFFSET $offset;";
}

// Returns the number of posts in the given query, or -1 if a failure occurs.
function CountNumPosts($search_string) {
    $sql = "SELECT count(*) FROM ".GALLERY_POST_TABLE." T WHERE ".CreateSQLClauses($search_string).";";
    if (!sql_query_into($result, $sql, 0)) return -1;
    if ($result->num_rows == 0) return -1;
    $row = $result->fetch_assoc();
    return $row['count(*)'];
}
?>