<?php

namespace controle;

use controle\Validador;
use modelo\Assunto;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ValidadorAssunto extends Validador {

    public $mensagem = null;

    public function __construct() {
        $this->entidade = new Assunto("", "");
    }

    public function validar($entidade) {
        $this->entidade = $entidade;


        if ($this->entidade->getDescricao() == null) {
            $mensagem = "Campo Descrição obrigatório!";
        }


        return $mensagem;
    }

}
