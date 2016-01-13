<?php

//Este script é utilizado para fazer a navegacao nas paginas de controle do 
//sistema
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/twig/twig/lib/Twig/Autoloader.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/bootstrap.php");

use controle\Controlador;
use controle\ControladorFactory;
use controle\Redirecionamento;
use dao\Dao;
use util\Util;

//Inicia a sessao 
session_start();

//Inicia o twig engine
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem("$_SERVER[DOCUMENT_ROOT]/sgp/src/visao");
$twig = new Twig_Environment($loader);

//Algoritmo para expirar a sessão após 30 minutes cajo não haja solicitação do usuário
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    redirecionarSimples($twig, "sessao_expirada.twig");
    return;
}
$_SESSION['LAST_ACTIVITY'] = time();

$visoes_navegacao = null;
if (isset($_SESSION['visoes_navegacao'])) {
    $visoes_navegacao = unserialize($_SESSION['visoes_navegacao']);
} else {
    //Nesse vetor sao colocas as páginas de navegacao do sitema
    $visoes_navegacao = array(
        Controlador::CTRL_FUNCIONARIO => 'forms/form_funcionario.twig',
        Controlador::CTRL_USUARIO => 'forms/form_usuario.twig',
        Controlador::CTRL_ASSUNTO => 'forms/form_assunto.twig',
        Controlador::CTRL_DEPARTAMENTO => 'forms/form_departamento.twig',
        Controlador::CTRL_MOVIMENTACAO => 'forms/form_movimentacao.twig',
        Controlador::CTRL_PROCESSO => 'forms/form_processo.twig',
        Controlador::CTRL_PROCESSO_MOVIMENTACAO => 'forms/form_processo_movimentacao.twig',
        Controlador::CTRL_HOME => 'home.twig'
    );
    //Pra inserir qualquer objeto na sessao é necessario serializa-lo, 
    //visoes_navegacao é a chave de mapeamento pra esse vetor, as variaveis de
    //sessao sao acessadas de todos os locais do sitema
    $_SESSION['visoes_navegacao'] = serialize($visoes_navegacao);
}

$controladores = array();
if (isset($_SESSION['controladores'])) {
    $controladores = unserialize($_SESSION['controladores']);
} else {
    $_SESSION['controladores'] = serialize($controladores);
}

//Para cada requisicao feita a esse script, é necessario saber se trata de um 
//link ou um comando de controle
$chaves = array_keys($_POST);
//Caso o front_controller seja chamado sem nenhuma funcao ou navegacao
// o usuario é redirecionado para a pagina home
if (empty($chaves)) {
    $chaves[] = "navegador_gerenciar_home";
}
if (!isset($controladores[Controlador::CTRL_AUTENTICACAO])) {
    $controladores[Controlador::CTRL_AUTENTICACAO] = ControladorFactory::criarControlador(
                    Controlador::CTRL_AUTENTICACAO, $entityManager);
}
$autenticacaoCtrl = $controladores[Controlador::CTRL_AUTENTICACAO];
foreach ($chaves as $requisicao) {
    if (is_string($requisicao)) {
        //Os link do sistema tem que começar com 'navegador_' seguido da visao 
        // a ser acessada
        $redirecionamento = new Redirecionamento();

        if (Util::startsWithString($requisicao, "navegador_")) {
            //remove a palavra navegador_
            $visao = str_replace("navegador_", "", $requisicao);
            //Verifica se essa visao existe no sistema, nesse caso no vetor
            //de visões
            if (isset($visoes_navegacao[$visao])) {
                $redirecionamento->setDestino($visoes_navegacao[$visao]);
                if (!isset($controladores[$visao])) {
                    $controladores[$visao] = ControladorFactory::criarControlador($visao, $entityManager);
                }
                $controlador = $controladores[$visao];
                $controlador->setDao(new Dao($entityManager));
                $redirecionamento->setCtrl($controlador);
                //Gera o template e manda renderizar a visao .twig
                redirecionar($twig, $redirecionamento, $autenticacaoCtrl);
                $_SESSION['controladores'] = serialize($controladores);
                return;
            }
        } else if (Util::startsWithString($requisicao, "funcao_")) {
            $ctrl = str_replace("funcao_", "", $requisicao);
            $funcao = $_POST[$requisicao];
            if (isset($controladores[$ctrl])) {
                $controlador = $controladores[$ctrl];
                $controlador->setDao(new Dao($entityManager));
                //passando os controladores pela funcao executar funcao para 
                //comunicao entre eles
                $redirecionamento = $controlador->executarFuncao(
                        $_POST, $funcao, $controladores);
                $redirecionamento->setDestino(
                        $visoes_navegacao[$redirecionamento->getDestino()]);
                redirecionar($twig, $redirecionamento, $autenticacaoCtrl);
                if ($controlador->getDao() != null) {
                    $controlador->getDao()->getEntityManager()->close();
                    $controlador->getDao()->setEntityManager(null);
                }
                $_SESSION['controladores'] = serialize($controladores);
                return;
            }
        }
    }
}

function redirecionar($twig, $redirecionamento, $autenticacaoCtrl) {
    $template = $twig->loadTemplate($redirecionamento->getDestino());
    if ($redirecionamento->getCtrl() != null) {
        $redirecionamento->getCtrl()->iniciar();
        print $template->render(array("ctrl" => $redirecionamento->getCtrl(),
                    "autenticacaoCtrl" => $autenticacaoCtrl));
        $autenticacaoCtrl->setVisaoAtual($redirecionamento->getCtrl()->getDescricao());
        //Apos o template ser renderizado com as informacoes do ctrl, alguns dados
        //como mensagem e validador sao apagados, pois so serve para exibida apenas 
        //uma vez
        $redirecionamento->getCtrl()->resetar();
    } else {
        redirecionarSimples($twig, "home.twig");
    }
}

function redirecionarSimples($twig, $destino) {
    $template = $twig->loadTemplate($destino);
    print $template->render(array());
}
