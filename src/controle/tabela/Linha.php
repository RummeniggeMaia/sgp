<?php

namespace controle\tabela;

/**
 * Description of Linha
 *
 * @author Rummenigge
 */
class Linha {

    private $valores;

    public function __construct() {
        $this->valores = array();
    }

    public function getValores() {
        return $this->valores;
    }

    public function setValores($valores) {
        $this->valores = $valores;
    }

}
