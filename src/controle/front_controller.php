<?php

require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/autoload.php");
require_once("$_SERVER[DOCUMENT_ROOT]/sgp/vendor/twig/twig/lib/Twig/Autoloader.php");

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem("$_SERVER[DOCUMENT_ROOT]/sgp/src/visao");
$twig = new Twig_Environment($loader);

if (isset($_POST['navegador_funcionario'])) {
    $template = $twig->loadTemplate('/forms/form_funcionario.twig');
    echo $template->render(array());
    return;
}
print "Navegação incorreta";
