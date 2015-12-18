<?php

namespace controle;

use util\Util;

/**
 *
 * @author Rummenigge
 */
abstract class Controlador {

    protected $entidade;
//    Copia da entidade utilizada apenas para armazenar o estado anterior no log
    protected $copiaEntidade;
    protected $entidades;
    protected $dao;
    protected $mensagem;
    protected $modeloTabela;
    protected $modoEditar;
    protected $modoBusca;
    //Controlador que receberá as entidades selecionadas, deve ser String 
    //apenas pra acessar o controlador de destino no vetor de controladores
    protected $ctrlDestino;
    //Variavel usada apenas acionar uma determinada tab na durante a resposta, 
    //quando o usuario clica em editar entao a tab de form tem q aparecer em vez
    // da tab que contem a tabela
    protected $tab = "tab_tabela";

    public function getEntidade() {
        return $this->entidade;
    }

    public function getAssunto() {
        return $this->assunto;
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

    public function setAssunto($assunto) {
        $this->assunto = $assunto;
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

    public function getModoEditar() {
        return $this->modoEditar;
    }

    public function setModoEditar($modoEditar) {
        $this->modoEditar = $modoEditar;
    }

    public function getCtrlDestino() {
        return $this->ctrlDestino;
    }

    public function setCtrlDestino($ctrlDestino) {
        $this->ctrlDestino = $ctrlDestino;
    }

    public function getModoBusca() {
        return $this->modoBusca;
    }

    public function setModoBusca($modoBusca) {
        $this->modoBusca = $modoBusca;
    }

    public function getTab() {
        return $this->tab;
    }

    public function setTab($tab) {
        $this->tab = $tab;
    }

    public function pesquisar() {
        if ($this->modeloTabela->getPaginador()->getPesquisa() != null) {
            $this->entidades = $this->dao->pesquisar(
                    $this->modeloTabela->getPaginador()->getPesquisa()
                    , $this->modeloTabela->getPaginador()->getLimit()
                    , $this->modeloTabela->getPaginador()->getOffset());
            $this->gerarLinhas();
        }
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
    }

    public abstract function executarFuncao($post, $funcao, $controladores);

    public abstract function gerarLinhas();
    
    public abstract function resetar();
}
