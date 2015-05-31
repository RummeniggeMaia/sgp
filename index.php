<?php

require_once "vendor/autoload.php";
require_once './vendor/twig/twig/lib/Twig/Autoloader.php';

session_start();

$visoes_navegacao = array(
    'visao_gerenciar_funcionario' => 'forms/form_funcionario.twig',
    'visao_gerenciar_assunto' => 'forms/form_assunto.twig',
    'visao_gerenciar_departamento' => 'forms/form_departamento.twig',
    'visao_home' => 'home.twig'
);

$_SESSION['visoes_navegacao'] = serialize($visoes_navegacao);

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('src/visao/');
$twig = new Twig_Environment($loader);

$template = $twig->loadTemplate($visoes_navegacao['visao_home']);
echo $template->render(
        array(
            'semantic_js' => 'web/semantic_ui/semantic.min.js',
            'semantic_css' => 'web/semantic_ui/semantic.min.css',
            'jquerylib' => 'web/jquery.min.js',
            'front_ctrl' => 'src/controle/front_controller.php',
            'logo' => 'web/imagens/icon-process-white.png')
);
