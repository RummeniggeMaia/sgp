<?php

require_once "vendor/autoload.php";
require_once './vendor/twig/twig/lib/Twig/Autoloader.php';

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('src/visao/');
$twig = new Twig_Environment($loader);

$template = $twig->loadTemplate('home.twig');
echo $template->render(
        array(
            'semantic_js' => 'web/semantic_ui/semantic.min.js',
            'semantic_css' => 'web/semantic_ui/semantic.min.css',
            'jquerylib' => 'web/jquery.min.js',
            'front_ctrl' => 'src/controle/front_controller.php',
            'logo' => 'web/imagens/icon-process-white.png')
);
