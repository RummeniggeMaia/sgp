<?php

namespace controle;

use controle\Controlador;

/**
 * Description of HomeCtrl
 *
 * @author Rummenigge
 */
class HomeCtrl extends Controlador {

    public function __construct() {
        $this->descricao = Controlador::CTRL_HOME;
    }

    public function executarFuncao($funcao) {
        
    }

    public function gerarLinhas() {
        
    }

    public function resetar() {
        parent::resetar();
    }

    public function iniciar() {
        
    }

}
