<?php

namespace controle\validadores;

use controle\Mensagem;
use controle\validadores\Validador;

class ValidadorDepartamento extends Validador {

    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = new Mensagem(
                'Dados inválidos'
                , Mensagem::MSG_TIPO_ERRO
                , 'Dados do departamento estão inválidos.');
        $submensagens = array();

        if ($this->entidade->getDescricao() == null) {
            $submensagens[] = "Campo Descrição obrigatório!\n";
            $this->camposInvalidos[] = "campo_descricao";
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

}
