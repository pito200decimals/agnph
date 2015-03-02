<?php
// Page for composing a new forum post.
// URL: /forums/create/
// URL: /forums/reply/{post-id}/

// Site includes, including login authentication.
include_once("../header.php");

if (!isset($user)) {
    // User is not logged in.
    $vars['error_msg'] = "Must be logged in to post.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if (!isset($_GET['create'])) {
    // Missing create boolean var.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if ($_GET['create'] != "true" && $_GET['create'] != "false") {
    // Invalid boolean parameter.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}
if ($_GET['create'] == "false" && !isset($_GET['post'])) {
    // Missing post id when we want to reply to a post.
    $vars['error_msg'] = "Invalid URL.";
    echo $twig->render("base.tpl", $vars);
    return;
}

if ($_POST) {
    // Try to submit user data. If invalid, fall back to the already-initialized compose form.
    // TODO: Submit compose data.
}

if ($_GET['create'] == "true") {
    // Create the form for composing a new message here.
    $vars['content'] = "Compose page, Creating new thread";
} else {
    // Create the form for composing a reply to an existing post here.
    $reply_post_id = $_GET['post'];
    $vars['content'] = "Compose page, Replying to post $reply_post_id";
}

//$vars['content'] = "Compose page";
echo $twig->render("base.tpl", $vars);
?>