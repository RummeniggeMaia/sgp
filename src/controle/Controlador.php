<?php

namespace controle;

/**
 *
 * @author Rummenigge
 */
abstract class Controlador {

    protected $funcionario;
    protected $aux;
    protected $funcionarios;
    protected $dao;
    protected $mensagem;
    protected $modeloTabela;

    public function getFuncionario() {
        return $this->funcionario;
    }

    public function getAux() {
        return $this->aux;
    }

    public function getFuncionarios() {
        return $this->funcionarios;
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

    public function setFuncionario($funcionario) {
        $this->funcionario = $funcionario;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }

    public function setFuncionarios($funcionarios) {
        $this->funcionarios = $funcionarios;
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

    public abstract function executarFuncao($post, $funcao);
}
