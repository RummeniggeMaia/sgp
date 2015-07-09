<?php

namespace dao;

use modelo\Funcionario;

/**
 * Description of DqlBuilder
 *
 * @author Rummenigge
 */
class DqlBuilder {

    public function gerarDql($entidade) {
        if ($entidade->getClassName() == "Funcionario") {
            return $this->dqlFuncionario($entidade);
        }
    }
    
    private function dqlFuncionario(Funcionario $funcionario) {
        $dql = "select f from Funcionario where 1";
        if ($funcionario->getNome() != null) {
            $dql . " and nome = " . $funcionario->getNome();
        }
        if ($funcionario->getCpf() != null) {
            $dql . " and nome = " . $funcionario->getCpf();
        }
        if ($funcionario->getRg() != null) {
            $dql . " and nome = " . $funcionario->getRg();
        }
        return $dql;
    }

}
