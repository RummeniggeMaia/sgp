<?php

namespace controle;

/**
 * Description of Redirecionamento
 *
 * @author Rummenigge
 */
class Redirecionamento {

    private $destino;
    private $ctrl;

    public function __construct() {
    }

    public function getDestino() {
        return $this->destino;
    }

    public function getCtrl() {
        return $this->ctrl;
    }

    public function setDestino($destino) {
        $this->destino = $destino;
    }

    public function setCtrl($ctrl) {
        $this->ctrl = $ctrl;
    }

}
