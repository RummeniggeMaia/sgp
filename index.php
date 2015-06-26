<?php

require_once "vendor/autoload.php";
require_once './vendor/twig/twig/lib/Twig/Autoloader.php';

//Função utilizada pra iniciar a sessão em scripts no php
session_start();

$controladores = null;
if (isset($_SESSION['controladores'])) {
    $controladores = unserialize($_SESSION['controladores']);
} else {
    $controladores = array();
    $funcionarioCtrl = new controle\FuncionarioCtrl();    
    $assuntoCtrl = new controle\AssuntoCtrl();
    $departamentoCtrl = new controle\DepartamentoCtrl();
    $movimentacaoCtrl = new controle\MovimentacaoCtrl();
    $controladores['gerenciar_funcionario'] = $funcionarioCtrl;
    $controladores['gerenciar_assunto'] = $assuntoCtrl;
    $controladores['gerenciar_departamento'] = $departamentoCtrl;
    $controladores['gerenciar_movimentacao'] = $movimentacaoCtrl;
    $controladores['gerenciar_processo'] = $processoCtrl;
    $_SESSION['controladores'] = serialize($controladores);
}

$visoes_navegacao = null;
if (isset($_SESSION['visoes_navegacao'])) {
    $visoes_navegacao = unserialize($_SESSION['visoes_navegacao']);
} else {
    //Nesse vetor sao colocas as páginas de navegacao do sitema
    $visoes_navegacao = array(
        'gerenciar_funcionario' => 'forms/form_funcionario.twig',
        'gerenciar_assunto' => 'forms/form_assunto.twig',
        'gerenciar_departamento' => 'forms/form_departamento.twig',
        'gerenciar_movimentacao' => 'forms/form_movimentacao.twig',
        'gerenciar_processo' => 'forms/form_processo.twig',
        'home' => 'home.twig'
    );
//Pra inserir qualquer objeto na sessao é necessario serializa-lo, 
//visoes_navegacao é a chave de mapeamento pra esse vetor, as variaveis de
//sessao sao acessadas de todos os locais do sitema
    $_SESSION['visoes_navegacao'] = serialize($visoes_navegacao);
}

//Inicia o twig
Twig_Autoloader::register();
//Carrega o diretorio dos templates, aqui é src/visao por causa do local do
//index.php
$loader = new Twig_Loader_Filesystem('src/visao/');
$twig = new Twig_Environment($loader);
//Gera a interface home de acordo com o template home.twig
$template = $twig->loadTemplate($visoes_navegacao['home']);
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
