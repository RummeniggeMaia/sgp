<?php

namespace modelo;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 *
 *
 * @Entity 
 * @Table(name="log")
 */
class Log extends Entidade {

    /*
     * Usuario logado no sistema
     */
    private $usuario;
    /**
     * Os tipos sao CADASTRO, EDICAO, REMOCAO e NULO, 
     */
    private $tipo;
    /*
     * Dados serão armazenados como uma string em JSON
     */
    private $dadosAltarados;
    
    const TIPO_CADASTRO = "TIPO_CADASTRO";
    const TIPO_EDICAO = "TIPO_EDICAO";
    const TIPO_REMOCAO = "TIPO_REMOCAO";

    public function getUsuario() {
        return $this->usuario;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getDadosAltarados() {
        return $this->dadosAltarados;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setDadosAltarados($dadosAltarados) {
        $this->dadosAltarados = $dadosAltarados;
    }

    public function clonar() {
        $clone = new Log();
        
        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);
        
        $clone->setUsuario($this->usuario->clonar());
        $clone->setTipo($this->tipo);
        $clone->setDadosAltarados($this->dadosAltarados);
        
        return $clone;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

}
