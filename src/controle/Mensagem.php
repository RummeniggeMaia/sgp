<?php

namespace controle;

/**
 * Description of Mensagem
 *
 * @author Rummenigge
 */
class Mensagem {

    //put your code here
    private $cabecalho;
    private $tipo;
    private $descricao;
    private $submensagens;

    const MSG_TIPO_OK = 'msg_tipo_ok';
    const MSG_TIPO_INFO = 'msg_tipo_info';
    const MSG_TIPO_AVISO = 'msg_tipo_aviso';
    const MSG_TIPO_ERRO = 'msg_tipo_erro';

    function __construct($cabecalho, $tipo, $descricao) {
        $this->cabecalho = $cabecalho;
        $this->tipo = $tipo;
        $this->descricao = $descricao;
        $this->submensagens = array();
    }

    public function getCabecalho() {
        return $this->cabecalho;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setCabecalho($cabecalho) {
        $this->cabecalho = $cabecalho;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getSubmensagens() {
        return $this->submensagens;
    }

    public function setSubmensagens($submensagens) {
        $this->submensagens = $submensagens;
    }

}
