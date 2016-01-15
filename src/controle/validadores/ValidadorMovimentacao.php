<?php

namespace controle\validadores;

use controle\Mensagem;
use controle\validadores\Validador;

class ValidadorMovimentacao extends Validador {

    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = new Mensagem(
                'Dados inválidos', Mensagem::MSG_TIPO_ERRO, 'Dados do movimentação estão inválidos.');
        $submensagens = array();

        if ($this->entidade->getDescricao() == null ||
                $this->entidade->getDescricao() == "") {
            $submensagens[] = "Campo Descrição obrigatório!\n";
            $this->camposInvalidos[] = "campo_descricao";
        } else if (!preg_match("/^([ a-zA-Z'\-áéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõçÇ])+$/i"
                        , $this->entidade->getDescricao())) {
            $submensagens[] = "Caracteres inválidos na descrição!\n";
            $this->camposInvalidos[] = "campo_descricao";
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

}
