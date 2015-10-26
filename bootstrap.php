<?php

/** Este script é a mesma coisa que o DaoUtil feito em Java, serve apenas para
 *  ter acesso ao EntityManager.
 */
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/src/modelo"), $isDevMode);

$conn = array(
    'driver' => 'pdo_mysql',
    'dbname' => 'sgp',
    'user' => 'root',
    'password' => '',
    'host' => 'localhost',
    'charset' => 'utf8',
);

// A ideia aqui é importar esse script nas classes no script de controle.
$entityManager = EntityManager::create($conn, $config);
