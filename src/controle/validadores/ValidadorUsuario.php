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
                'Dados inválidos', Mensagem::MSG_TIPO_ERRO
                , 'Dados do usuário estão inválidos.');
        $submensagens = array();

        if ($this->entidade->getNome() == null ||
                $this->entidade->getNome() == "") {
            $submensagens[] = "Campo Nome obrigatório!\n";
            $this->camposInvalidos[] = "campo_nome";
        } else if (strlen($this->entidade->getNome()) < 4) {
            $submensagens[] = "Campo Nome tem que ter ao menos 4 letras!\n";
            $this->camposInvalidos[] = "campo_nome";
        } else if (!preg_match("/^([ a-zA-Z'\-áéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõçÇ])+$/i"
                        , $this->entidade->getNome())) {
            $submensagens[] = "Caracteres inválidos no nome!\n";
            $this->camposInvalidos[] = "campo_nome";
        }
        if ($this->entidade->getEmail() == null || $this->entidade->getEmail() == "") {
            $submensagens[] = "Campo E-mail obrigatório";
            $this->camposInvalidos[] = "campo_email";
        } else if (!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i"
                        , $this->entidade->getEmail())) {
            $submensagens[] = "E-mail inválido!\n";
            $this->camposInvalidos[] = "campo_email";
        }
        if ($this->entidade->getLogin() == null || $this->entidade->getLogin() == "") {
            $submensagens[] = "Campo Login obrigatório!\n";
            $this->camposInvalidos[] = "campo_login";
        }
        if ($this->entidade->getSenha() == null || $this->entidade->getSenha() == "") {
            $submensagens[] = "Campo Senha obrigatório!\n";
            $this->camposInvalidos[] = "campo_senha";
        } else if (strlen($this->entidade->getNome()) < 4) {
            $submensagens[] = "Senha muito curta. Deve ter ao menos 4 caracteres!\n";
            $this->camposInvalidos[] = "campo_senha";
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

}
