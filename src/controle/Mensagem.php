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

    function __construct($cabecalho, $tipo, $descricao) {
        $this->cabecalho = $cabecalho;
        $this->tipo = $tipo;
        $this->descricao = $descricao;
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

}
