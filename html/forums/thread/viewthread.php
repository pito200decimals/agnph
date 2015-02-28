<?php
// Page where a user can view a thread.
// URL format: /forums/thread/{thread-id}/{page}/#p{post-id}
// Rewritten format: /forums/thread/viewthread.php?t={thread-id}&p={page}#p{post-id}

// Site includes, including login authentication.
include_once("../../header.php");

// MOCKUP: Thread 1.
$_GET['t'] = 1;

if (!isset($_GET['t'])) {
    // No thread, quit.
    $vars['content'] = "Thread not found.";
    RenderPage("forums/thread/viewthread.tpl");
    return;
}
$tid = $_GET['t'];
if (!isset($_GET['p'])) {
    // No page set, just assume page 1.
    $page = 1;
} else {
    $page = $_GET['p'];
}

// TODO: Get thread content.
$thread = array(
        'title' => "The best thread in the world!",
        'posts' => array(),
    );
// TODO: Write out thread title and other info?

// MOCKUP: Post 10 messages.
for ($i = 0; $i < 30; $i++) {
    //$poster = $user;
    $poster = array();
    LoadUser(($i % 3) + 1, $poster);
    $post = array('id' => $i, 'poster' => $poster, 'content' => "This is message $i");
    if ($i >= 12) $post['new'] = true;
    $thread['posts'][] = $post;
}

// Default content.
$vars['content'] = "Thread not found";

// Render page template.
$vars['thread'] = $thread;
RenderPage("forums/thread/viewthread.tpl");
?>