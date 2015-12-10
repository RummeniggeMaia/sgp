<?php

namespace controle\validadores;

use controle\validadores\Validador;
use modelo\Assunto;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ValidadorAssunto extends Validador {

    public function __construct() {
        $this->entidade = new Assunto("", "");
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
        $index = intval(str_replace("editar_", "", $funcao));
        return $index;
    }

    public function validar($entidade) {
        
    }

}
