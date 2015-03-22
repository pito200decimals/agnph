<?php
// General functions related to searching for posts.

function CreatePostSearchSQL($search_string) {
    if (strlen($search_string) > 0) {
        $tag_names = explode(" ", $search_string);
        $tags = GetTagsByName($tag_names);
        if ($tags && sizeof($tags) > 0) {
            $and_clauses = array_map(function($tag) {
                return "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." WHERE T.PostId=PostId AND TagId=".$tag['TagId'].")";
            }, $tags);
            $joined_and_clauses = implode(" AND ", $and_clauses);
            return "SELECT * FROM ".GALLERY_POST_TABLE." T WHERE $joined_and_clauses ORDER BY PostId DESC;";
            sql_query_into($result, "SELECT PostId FROM ".GALLERY_POST_TAG_TABLE." T WHERE $joined_and_clauses ORDER BY PostId DESC;", 0) or RenderErrorPage("No posts found");
            $post_ids = array();
            while ($row = $result->fetch_assoc()) {
                $post_ids[] = $row['PostId'];
            }
            $post_ids = array_unique($post_ids);
            if (sizeof($post_ids) > 0) {
                $joined = implode(",", $post_ids);
                return "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId IN ($joined) ORDER BY PostId DESC;";
            } else {
                // No posts to show.
                return "SELECT * FROM ".GALLERY_POST_TABLE." WHERE 0;";
            }
        } else {
            // No posts to show.
            return "SELECT * FROM ".GALLERY_POST_TABLE." WHERE 0;";
        }
    } else {
        // Main index.
        return "SELECT * FROM ".GALLERY_POST_TABLE." ORDER BY PostId DESC;";
    }
}
?>