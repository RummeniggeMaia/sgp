<?php

namespace controle;

use controle\Controlador;
use dao\Dao;
use modelo\Departamento;
use controle\Mensagem;

/**
 * Description of DepartamentoCtrl
 *
 * @author Rummenigge
 */
class DepartamentoCtrl extends Controlador {

    public function __construct() {
        $this->departamento = new Departamento("", "");
        $this->aux = new Departamento("", "");
        $this->departamentos = array();
        $this->mensagem = null;
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
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

    public function getDepartamentos() {
        return $this->departamentos;
    }

    /**
     * Factory method para gerar departamentos baseado a partir do POST
     */
    public function gerarDepartamento($post) {
        if (isset($post['campo_descricao'])) {
            $this->departamento->setDescricao($post['campo_descricao']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarDepartamento($post);
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->departamento);
            $this->departamento = new Departamento("", "");
            $this->mensagem = new Mensagem(
                    "Cadastro de Departamentos"
                    , "msg_tipo_ok"
                    , "Departamento cadastrado com sucesso.");
            return 'gerenciar_departamento';
        } else {
            return false;
        }
    }

    public function gerarLinhas() {
        
    }

}
