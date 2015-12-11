<?php

namespace validadores;

use modelo\Movimentacao;
use validadores\Validador;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ValidadorMovimentacao extends Validador {

    public function __construct() {
        $this->entidade = new Movimentacao(null, "", "");
    }

    public function validar($entidade) {
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
