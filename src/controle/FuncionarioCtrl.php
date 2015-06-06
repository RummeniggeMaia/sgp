<?php

use modelo\Funcionario;

/**
 * Description of FuncionarioCtrl
 *
 * @author Rummenigge
 */
class FuncionarioCtrl {

    private $funcionario;
    private $aux;
    private $funcionarios;

    public function __construct() {
        $this->funcionario = new Funcionario("", "", "");
        $this->aux = new Funcionario("", "", "");
        $this->funcionarios = array();
    }

    public function getFuncionario() {
        return $this->funcionario;
    }

    public function getAux() {
        return $this->aux;
    }

    public function setFuncionario($funcionario) {
        $this->funcionario = $funcionario;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }
    
    public function adicionarFuncionario($funcionario) {
        $this->funcionarios[$funcionario->getId()] = $funcionario;
    }
    
    public function removerFuncionario($funcionario) {
        unset($this->funcionarios[$funcionario->getId()]);
    }

    /**
     * Factory method para gerar funcionarios baseado a partir do POST
     */
    public function gerarFuncionario($post) {
        if (isset($post['campo_nome'])) {
            $this->funcionario->setNome($post['campo_nome']);
        }
        if (isset($post['campo_cpf'])) {
            $this->funcionario->setNome($post['campo_cpf']);
        }
        if (isset($post['campo_rg'])) {
            $this->funcionario->setNome($post['campo_rg']);
        }
    }

}
