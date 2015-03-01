<?php
// External page for viewing a board (list of threads).
// TODO: Decide if this should also handle a lobby/subforum with a list of boards.

// Site includes, including login authentication.
include_once("header.php");

//if (!isset($_GET[''])) {
//    debug_die("");
//}

// Pages will set their content here.
$content = "";

// This is how to output the template.
$vars['content'] = $content;
echo $twig->render("index.tpl", $vars);
?>