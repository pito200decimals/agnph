<?php

// Site includes, including login authentication.
include_once(__DIR__."/include/config.php");
include_once(__DIR__."/include/constants.php");
include_once(__DIR__."/include/auth/auth.php");
include_once(__DIR__."/include/util/core.php");

// Template engine includes.
include_once(__DIR__."/../lib/Twig/Autoloader.php");
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(__DIR__."/templates/");
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__."/cache",
));

$template = $twig->loadTemplate('index.tpl');

echo $template->render(array(
	'navigation' => array(
		array('href' => "href 1", 'caption' => "caption 1"),
		array('href' => "href 2", 'caption' => "caption 2")),
	'footer' => 'FOOTER'));
?>