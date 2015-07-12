<?php

namespace controle\tabela;

use controle\tabela\Paginador;
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
    private $paginador;
    
    public function __construct() {
        $this->cabecalhos = array();
        $this->linhas = array();
        $this->modoBusca = false;
        $this->paginador = new Paginador();
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
    public function getPaginador() {
        return $this->paginador;
    }

    public function setPaginador($paginador) {
        $this->paginador = $paginador;
    }
}
