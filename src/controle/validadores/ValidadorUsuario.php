<?php

namespace controle\validadores;

use controle\Mensagem;
use controle\validadores\Validador;

class ValidadorUsuario extends Validador {

    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = new Mensagem(
                'Dados inválidos', Mensagem::MSG_TIPO_ERRO, 'Dados do usuário estão inválidos.');
        $submensagens = array();

        if ($this->entidade->getLogin() == null) {
            $submensagens[] = "Campo Login obrigatório!\n";
            $this->camposInvalidos[] = "campo_login";
        }
        if ($this->entidade->getSenha() == null) {
            $submensagens[] = "Campo Senha obrigatório!\n";
            $this->camposInvalidos[] = "campo_senha";
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

}
