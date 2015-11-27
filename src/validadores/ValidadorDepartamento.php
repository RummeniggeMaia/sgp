<?php

namespace validadores;

use validadores\Validador;
use modelo\Departamento;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ValidadorDepartamento extends Validador {

    public function __construct() {
        $this->entidade = new Departamento("", "");
    }

    public function validarCadastro($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = null;

        if ($this->entidade->getDescricao() == null) {
            $this->mensagem = "Campo Descrição obrigatório!";
        }


        return $this->mensagem;
    }

    public function validarEdicao($funcao) {
        
    }

}