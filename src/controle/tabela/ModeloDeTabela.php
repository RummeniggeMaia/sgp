<?php

namespace controle\tabela;
/**
 * Description of ModeloDeTabela
 *
 * @author Rummenigge
 */
class ModeloDeTabela {

    //put your code here
    private $cabecalhos;
    private $linhas;
    private $modoBusca;
    
    public function __construct() {
        $this->cabecalhos = array();
        $this->linhas = array();
        $this->modoBusca = false;
    }
    
    public function getCabecalhos() {
        return $this->cabecalhos;
    }

    public function getLinhas() {
        return $this->linhas;
    }

    public function getModoBusca() {
        return $this->modoBusca;
    }

    public function setCabecalhos($cabecalhos) {
        $this->cabecalhos = $cabecalhos;
    }

    public function setLinhas($linhas) {
        $this->linhas = $linhas;
    }

    public function setModoBusca($modoBusca) {
        $this->modoBusca = $modoBusca;
    }
}
