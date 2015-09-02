<?php

//Este script é utilizado para fazer a navegacao nas paginas de controle do 
//sistema
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/twig/twig/lib/Twig/Autoloader.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/bootstrap.php");

use controle\Redirecionamento;
use dao\Dao;
use util\Util;

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
        $redirecionamento = new Redirecionamento();
        if (Util::startsWithString($requisicao, "navegador_")) {
            //remove a palavra navegador_
            $visao = str_replace("navegador_", "", $requisicao);
            //Verifica se essa visao existe no sistema, nesse caso no vetor
            //de visões
            if (isset($visoes_navegacao[$visao])) {
                $redirecionamento->setDestino($visoes_navegacao[$visao]);
                $redirecionamento->setCtrl(
                        isset($controladores[$visao]) ?
                                $controladores[$visao] :
                                null
                );
                //Gera o template e manda renderizar a visao .twig
                redirecionar($twig, $redirecionamento);
                return;
            }
        } else if (Util::startsWithString($requisicao, "funcao_")) {
            $ctrl = str_replace("funcao_", "", $requisicao);
            $funcao = $_POST[$requisicao];
            if (isset($controladores[$ctrl])) {
                $controlador = $controladores[$ctrl];
                $controlador->setDao(new Dao($entityManager));
                //passando os controladores pela funcao executar funcao para comunicao entre eles
                $redirecionamento = $controlador->executarFuncao(
                        $_POST, $funcao, $controladores);
                $redirecionamento->setDestino(
                        $visoes_navegacao[$redirecionamento->getDestino()]);
                $controlador->getDao()->getEntityManager()->close();
                $controlador->getDao()->setEntityManager(null);
                $_SESSION['controladores'] = serialize($controladores);
                redirecionar($twig, $redirecionamento);
                return;
            }
        }
    }
}

function redirecionar($twig, $redirecionamento) {
    $template = $twig->loadTemplate($redirecionamento->getDestino());
    if ($redirecionamento->getCtrl() != null) {
        print $template->render(array("ctrl" => $redirecionamento->getCtrl()));
        //Apos o template ser renderizado com as informacoes do ctrl, a mensagem
        //é apagada, pois so serve para exibida apenas uma vez
        $redirecionamento->getCtrl()->setMensagem(null);
    } else {
        print $template->render(array());
    }
}
