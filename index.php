<?php

require_once "vendor/autoload.php";
require_once './vendor/twig/twig/lib/Twig/Autoloader.php';
require_once("bootstrap.php");

use dao\Dao;

//Função utilizada pra iniciar a sessão em scripts no php
session_start();

$controladores = null;
if (isset($_SESSION['controladores'])) {
    $controladores = unserialize($_SESSION['controladores']);
} else {
    $dao = new Dao($entityManager);
    $controladores = array();
    $funcionarioCtrl = new controle\FuncionarioCtrl();    
    $assuntoCtrl = new controle\AssuntoCtrl();
    $departamentoCtrl = new controle\DepartamentoCtrl();
    $movimentacaoCtrl = new controle\MovimentacaoCtrl();
    $usuarioCtrl = new controle\UsuarioCtrl();
    $autenticacaoCtrl = new controle\AutenticacaoCtrl();
    //No contrutor do ProcessoCtrl e ProcessoMovimentacaoCtrlsao pesquisadas as 
    //listas de assuntos, departamentos e movimentacoes, por isso passa-se o 
    //DAO pelo contrutor
    $processoCtrl = new controle\ProcessoCtrl($dao);
    $processoMovimentacaoCtrl = new controle\ProcessoMovimentacaoCtrl($dao);
    $homeCtrl = new controle\HomeCtrl();
    
    $controladores['gerenciar_funcionario'] = $funcionarioCtrl;
    $controladores['gerenciar_usuario'] = $usuarioCtrl;
    $controladores['gerenciar_assunto'] = $assuntoCtrl;
    $controladores['gerenciar_departamento'] = $departamentoCtrl;
    $controladores['gerenciar_movimentacao'] = $movimentacaoCtrl;
    $controladores['gerenciar_processo'] = $processoCtrl;
    $controladores['gerenciar_processo_movimentacao'] = $processoMovimentacaoCtrl;
    $controladores['gerenciar_home'] = $homeCtrl;
    $controladores['gerenciar_autenticacao'] = $autenticacaoCtrl;
    $_SESSION['controladores'] = serialize($controladores);
}

$visoes_navegacao = null;
if (isset($_SESSION['visoes_navegacao'])) {
    $visoes_navegacao = unserialize($_SESSION['visoes_navegacao']);
} else {
    //Nesse vetor sao colocas as páginas de navegacao do sitema
    $visoes_navegacao = array(
        'gerenciar_funcionario' => 'forms/form_funcionario.twig',
        'gerenciar_usuario' => 'forms/form_usuario.twig',
        'gerenciar_assunto' => 'forms/form_assunto.twig',
        'gerenciar_departamento' => 'forms/form_departamento.twig',
        'gerenciar_movimentacao' => 'forms/form_movimentacao.twig',
        'gerenciar_processo' => 'forms/form_processo.twig',
        'gerenciar_processo_movimentacao' => 'forms/form_processo_movimentacao.twig',
        'gerenciar_home' => 'home.twig'
    );
    //Pra inserir qualquer objeto na sessao é necessario serializa-lo, 
    //visoes_navegacao é a chave de mapeamento pra esse vetor, as variaveis de
    //sessao sao acessadas de todos os locais do sitema
    $_SESSION['visoes_navegacao'] = serialize($visoes_navegacao);
}

// Apos os recursos do sistema serem carregados na sessao, o sistema 
// redirecionara o usuario para a pagina home, o front controller se encarregara 
// de carregar a pagina home.
header("Location: src/controle/front_controller.php");
