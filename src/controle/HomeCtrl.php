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

    public function executarFuncao($post, $funcao,& $controladores) {
        
    }

    public function gerarLinhas() {
        
    }

    public function resetar() {
        $this->mensagem = null;
        $this->dao = null;
    }

    public function iniciar() {
        
    }

}
