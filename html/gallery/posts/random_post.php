<?php
// Page for redirecting to a random post.
// URL: /gallery/post/random/
// URL: /gallery/posts/random.php

include_once("../../header.php");

define("MAX_TRIES", 100);

// Assume gallery post ids are relatively dense.
sql_query_into($result, "SELECT PostId FROM ".GALLERY_POST_TABLE." WHERE Status<>'D' ORDER BY PostId DESC LIMIT 1;", 1) or RenderErrorPage("Post not found");
$max_pid = $result->fetch_assoc()['PostId'];
for ($i = 0; $i < MAX_TRIES; $i++) {
    $rand_pid = mt_rand(1, $max_pid);
    if (sql_query_into($result, "SELECT PostId from ".GALLERY_POST_TABLE." WHERE PostId=$rand_pid AND Status<>'D' LIMIT 1;", 1)) {
        $pid = $result->fetch_assoc()['PostId'];
        header("Location: /gallery/post/show/$pid/");
        exit();
    }
}
RenderErrorPage("Post not found");
?>