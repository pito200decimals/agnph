<?php

// Site includes, including login authentication.
include_once("header.php");

// Pages will set their content here.
$content = "";

// This is how to output the template.
$vars['content'] = $content;
echo $twig->render('index.tpl', $vars);
?>