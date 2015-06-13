<?php

namespace controle;

use controle\Controlador;
use dao\Dao;
use modelo\Departamento;

/**
 * Description of DepartamentoCtrl
 *
 * @author Rummenigge
 */
class DepartamentoCtrl implements Controlador {

    private $departamento;
    private $aux;
    private $departamentos;
    private $dao;

    public function __construct() {
        $this->departamento = new Departamento("", "");
        $this->aux = new Departamento("", "");
        $this->departamentos = array();
    }

    public function getDao() {
        return $this->dao;
    }

    public function setDao($dao) {
        $this->dao = $dao;
    }

    public function getDepartamento() {
        return $this->departamento;
    }

    public function getAux() {
        return $this->aux;
    }

    public function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }

    public function adicionarDepartamento($departamento) {
        $this->departamento[$departamento->getId()] = $departamento;
    }

    public function removerDepartamento($departamento) {
        unset($this->departamento[$departamento->getId()]);
    }

    /**
     * Factory method para gerar departamentos baseado a partir do POST
     */
    public function gerarDepartamento($post) {
        if (isset($post['campo_constante'])) {
            $this->departamento->setConstante($post['campo_constante']);
        }
        if (isset($post['campo_descricao'])) {
            $this->departamento->setDescricao($post['campo_descricao']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarDepartamento($post);
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->departamento);
            $this->departamento = new Departamento("", "");
        }
    }

}

