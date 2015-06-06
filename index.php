<?php

require_once "vendor/autoload.php";
require_once './vendor/twig/twig/lib/Twig/Autoloader.php';

//Função utilizada pra iniciar a sessão em scripts no php
session_start();

//Nesse vetor sao colocas as páginas de navegacao do sitema
$visoes_navegacao = array(
    'visao_gerenciar_funcionario' => 'forms/form_funcionario.twig',
    'visao_gerenciar_assunto' => 'forms/form_assunto.twig',
    'visao_gerenciar_departamento' => 'forms/form_departamento.twig',    
    'visao_gerenciar_movimentacao' => 'forms/form_movimentacao.twig',       
    'visao_gerenciar_processo' => 'forms/form_processo.twig',
    'visao_home' => 'home.twig'
);
$controladores = array();
$funcionarioCtrl = new \modelo\Funcionario();

//Pra inserir qualquer objeto na sessao é necessario serializa-lo, 
//visoes_navegacao é a chave de mapeamento pra esse vetor, as variaveis de
//sessao sao acessadas de todos os locais do sitema
$_SESSION['visoes_navegacao'] = serialize($visoes_navegacao);
//Inicia o twig
Twig_Autoloader::register();
//Carrega o diretorio dos templates, aqui é src/visao por causa do local do
//index.php
$loader = new Twig_Loader_Filesystem('src/visao/');
$twig = new Twig_Environment($loader);
//Gera a interface home de acordo com o template home.twig
$template = $twig->loadTemplate($visoes_navegacao['visao_home']);
//Renderiza a pagina home de acordo com o template. O vetor serve para passar
//os diretorios dos arquivos web.
print $template->render(
        array(
            'semantic_js' => 'web/semantic_ui/semantic.min.js',
            'semantic_css' => 'web/semantic_ui/semantic.min.css',
            'jquerylib' => 'web/jquery.min.js',
            'front_ctrl' => 'src/controle/front_controller.php',
            'logo' => 'web/imagens/icon-process-white.png')
);
