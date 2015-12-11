<?php

namespace controle\validadores;

use controle\Mensagem;

/**
 *
 * @author Rummenigge
 */
abstract class Validador {

    protected $entidade;
    protected $mensagem;
    protected $camposInvalidos;
    protected $valido;

    public function getEntidade() {
        return $this->entidade;
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function getCamposInvalidos() {
        return $this->camposInvalidos;
    }

    public function setEntidade($entidade) {
        $this->entidade = $entidade;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
    }

    public function setCamposInvalidos($campos) {
        $this->camposInvalidos = $campos;
    }

    public function getValido() {
        return $this->valido;
    }

    public function setValido($valido) {
        $this->valido = $valido;
    }

    public abstract function validar($entidade);
}
