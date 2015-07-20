<?php

namespace controle;

use util\Util;

/**
 *
 * @author Rummenigge
 */
abstract class Controlador {

    protected $entidade;
    protected $aux;
    protected $entidades;
    protected $dao;
    protected $mensagem;
    protected $modeloTabela;

    public function getEntidade() {
        return $this->entidade;
    }
    
    public function getAssunto(){
        return $this->assunto;
    }

    public function getAux() {
        return $this->aux;
    }

    public function getEntidades() {
        return $this->entidades;
    }

    public function getDao() {
        return $this->dao;
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function getModeloTabela() {
        return $this->modeloTabela;
    }

    public function setEntidade($entidade) {
        $this->entidade = $entidade;
    }
    
    public function setAssunto($assunto){
        $this->assunto = $assunto;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }

    public function setEntidades($entidades) {
        $this->entidades = $entidades;
    }

    public function setDao($dao) {
        $this->dao = $dao;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
    }

    public function setModeloTabela($modeloTabela) {
        $this->modeloTabela = $modeloTabela;
    }

    public function pesquisar() {

        $this->entidades = $this->dao->pesquisar(
                $this->modeloTabela->getPaginador()->getPesquisa()
                , $this->modeloTabela->getPaginador()->getLimit()
                , $this->modeloTabela->getPaginador()->getOffset());
        $this->gerarLinhas();
    }

    public function paginar($acao) {
        $paginador = $this->modeloTabela->getPaginador();
        if ($acao == "paginador_primeira") {
            $paginador->primeira();
        } else if ($acao == "paginador_anterior") {
            $paginador->anterior();
        } else if ($acao == "paginador_proxima") {
            $paginador->proxima();
        } else if ($acao == "paginador_ultima") {
            $paginador->ultima();
        } else if (Util::startsWithString($acao, "paginador_pular_")) {
            $pagina = str_replace("paginador_pular_", "", $acao);
            $paginador->pular($pagina);
        } else if (Util::startsWithString($acao, "paginador_limit_")) {
            $limit = str_replace("paginador_limit_", "", $acao);
            if ($paginador->getLimit() != $limit) {
                $paginador->setOffset(0);
                $paginador->setLimit($limit);
                $paginador->setContagem(
                    $this->dao->contar($this->entidade));
            }
        }
        $this->pesquisar();
        return 'gerenciar_funcionario';
    }

    public abstract function executarFuncao($post, $funcao);
    
    public abstract function gerarLinhas();
}
