<?php
//Este script é utilizado para fazer a navegacao do 
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/twig/twig/lib/Twig/Autoloader.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/src/util/Util.php");
//Inicia a sessao novamente
session_start();
//Acessa o vetor de navegacao criado no index.php
$visoes_navegacao = unserialize($_SESSION["visoes_navegacao"]);

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem("$_SERVER[DOCUMENT_ROOT]/sgp/src/visao");
$twig = new Twig_Environment($loader);
//Para cada requisicao feita a esse script, é necessario saber se trata de um 
//link ou um comando de controle
foreach ($_POST as $requisicao) {
    if (is_string($requisicao)) {
        //Os link do sistema tem que começar com 'navegador_' seguido da visao 
        // a ser acessada
        if (Util::starsWithString($requisicao, "navegador_")) {
            //remove a palavra navegador_
            $visao = str_replace("navegador_", "", $requisicao);
            //Verifica se essa visao existe no sistema, nesse caso no vetor
            //de visões
            if (isset($visoes_navegacao[$visao])) {
                //Gera o template e manda renderizar a visao .twig
                $template = $twig->loadTemplate($visoes_navegacao[$visao]);
                print $template->render(array());
                return;
            }
        }
    }
}
print "Navegação incorreta";
return;
