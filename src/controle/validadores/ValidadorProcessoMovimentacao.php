<?php

/**
 * Description of ValidadorProcessoMovimentacao
 *
 * @author Rummenigge
 */
class ValidadorProcessoMovimentacao {
    //put your code here
    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = new Mensagem(
                'Dados inválidos', 
                Mensagem::MSG_TIPO_ERRO, 
                'Dados da Movimentação estão inválidos.');
        $submensagens = array();

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }
}
