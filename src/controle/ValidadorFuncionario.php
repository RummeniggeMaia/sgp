<?php

namespace controle;

use controle\Validador;
use modelo\Funcionario;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ValidadorFuncionario extends Validador {

    public function __construct() {
        $this->entidade = new Funcionario("", "", "");        
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = null;


        if ($this->entidade->getCpf() == null) {
            $this->mensagem = "Campo CPF obrigatório!";
        }

        if ($this->entidade->getRg() == null) {
            $this->mensagem = "Campo RG obrigatório!";
        }

        if ($this->entidade->getNome() == null) {
            $this->mensagem = "Campo Nome obrigatório!";
        }

        if ($this->entidade->getCpf() == null && $this->entidade->getRg() == null) {
            $this->mensagem = "Os campos CPF e RG são obrigatórios!";
        }

        if ($this->entidade->getCpf() == null && $this->entidade->getNome() == null) {
            $this->mensagem = "Os campos Nome e CPF são obrigatórios!";
        }

        if ($this->entidade->getNome() == null && $this->entidade->getRg() == null) {
            $this->mensagem = "Os campos Nome e RG são obrigatórios!";
        }

        if ($this->entidade->getNome() == null && $this->entidade->getRg() == null && $this->entidade->getCpf() == null) {
            $this->mensagem = "Os campos Nome, RG e CPF são obrigatórios!";
        }

        return $this->mensagem;
    }

}
