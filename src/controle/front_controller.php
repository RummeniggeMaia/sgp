<?php

//Este script é utilizado para fazer a navegacao nas paginas de controle do 
//sistema
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/twig/twig/lib/Twig/Autoloader.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/bootstrap.php");

use util\Util;
use dao\Dao;
use controle\FuncionarioCtrl;
use controle\AssuntoCtrl;
use controle\DepartamentoCtrl;
use controle\MovimentacaoCtrl;

//Inicia a sessao novamente
session_start();
//Acessa o vetor de navegacao criado no index.php
$visoes_navegacao = unserialize($_SESSION["visoes_navegacao"]);
$controladores = unserialize($_SESSION["controladores"]);

//Inicia o twig engine
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem("$_SERVER[DOCUMENT_ROOT]/sgp/src/visao");
$twig = new Twig_Environment($loader);

//Para cada requisicao feita a esse script, é necessario saber se trata de um 
//link ou um comando de controle
$chaves = array_keys($_POST);
foreach ($chaves as $requisicao) {
    if (is_string($requisicao)) {
        //Os link do sistema tem que começar com 'navegador_' seguido da visao 
        // a ser acessada
        if (Util::startsWithString($requisicao, "navegador_")) {
            //remove a palavra navegador_
            $visao = str_replace("navegador_", "", $requisicao);
            //Verifica se essa visao existe no sistema, nesse caso no vetor
            //de visões
            if (isset($visoes_navegacao[$visao])) {
                //Gera o template e manda renderizar a visao .twig
                redirecionar(
                        $visoes_navegacao[$visao]
                        , $twig
                        , isset($controladores[$visao]) ?
                                $controladores[$visao] :
                                null);
                return;
            }
        } else if (Util::startsWithString($requisicao, "funcao_")) { //funcao_gerenciar_assunto
            $ctrl = str_replace("funcao_", "", $requisicao); // gerenciar_assunto
            $funcao = $_POST[$requisicao];  // cadastrar
            if (isset($controladores[$ctrl])) {
                $controlador = $controladores[$ctrl];
                $controlador->setDao(new Dao($entityManager));
                $redirecionamento = $controlador->executarFuncao($_POST, $funcao);
                $controlador->getDao()->getEntityManager()->close();
                $controlador->getDao()->setEntityManager(null);
                redirecionar(
                        $visoes_navegacao[$redirecionamento]
                        , $twig
                        , $controlador);
                $_SESSION['controladores'] = serialize($controladores);
                return;
            }
        }
    }
}

function redirecionar($visao, $twig, $ctrl) {
    $template = $twig->loadTemplate($visao);
    if ($ctrl != null) {
        print $template->render(array("ctrl" => $ctrl));
        //Apos o template ser renderizado com as informacoes do ctrl, a mensagem
        //é apaga, pos so serve para exibida apenas uma vez
        $ctrl->setMensagem(null);
    } else {
        print $template->render(array());
    }
}
