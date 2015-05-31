<?php

require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/twig/twig/lib/Twig/Autoloader.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/src/util/Util.php");

session_start();

$visoes_navegacao = unserialize($_SESSION["visoes_navegacao"]);

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem("$_SERVER[DOCUMENT_ROOT]/sgp/src/visao");
$twig = new Twig_Environment($loader);

foreach ($_POST as $requisicao) {
    if (is_string($requisicao)) {
        if (Util::starsWithString($requisicao, "navegador_")) {
            $visao = str_replace("navegador_", "", $requisicao);
            if (isset($visoes_navegacao[$visao])) {
                $template = $twig->loadTemplate($visoes_navegacao[$visao]);
                print $template->render(array());
                return;
            }
        }
    }
}
print "Navegação incorreta";
return;
