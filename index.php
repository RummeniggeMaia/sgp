<?php

require_once 'bootstrap.php';
require_once './vendor/twig/twig/lib/Twig/Autoloader.php';

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('web/');
$twig = new Twig_Environment($loader);

$template = $twig->loadTemplate('home.twig');
echo $template->render(array());
