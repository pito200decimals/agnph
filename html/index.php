<?php

// Site includes, including login authentication.
include_once("header.php");
include_once(SITE_ROOT."include/auth/auth.php");

// Template engine includes.
include_once(__DIR__."/../lib/Twig/Autoloader.php");
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(__DIR__."/templates/");
$twig = new Twig_Environment($loader);

$template = $twig->loadTemplate('index.tpl');

echo $template->render(array(
    'navigation' => array(
        array('href' => "href 1", 'caption' => "caption 1"),
        array('href' => "href 2", 'caption' => "caption 2")),
    'footer' => 'FOOTER TEXT'));
?>