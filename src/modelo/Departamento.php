<?php

namespace modelo;

use modelo\Entidade;
use modelo\Processo;

/**
 *
 * @author Rummenigge
 * @Entity
 * @Table(name="departamento")
 */
class Departamento extends Entidade {

    /** @Column(type="boolean") */
    protected $constante;

    /** @Column(type="string") */
    protected $descricao;

    /** @OneToMany(targetEntity="Processo", mappedBy="departamento") */
    protected $processos;

    function __construct($descricao, $constante) {
        $this->constante = $constante;
        $this->descricao = $descricao;
        $this->processos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getConstante() {
        return $this->constante;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setConstante($constante) {
        $this->constante = $constante;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getProcessos() {
        return $this->processos;
    }

    public function setProcessos($processos) {
        $this->processos = $processos;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

    public function clonar() {
        $clone = new Departamento("", false);
        
        $clone->setId($this->id);
        $clone->setAtivo($this->ativo);
        $clone->setIndice($this->indice);
        $clone->setSelecionado($this->selecionado);
        
        $clone->setConstante($this->constante);
        $clone->setDescricao($this->descricao);
        
        return $clone;
    }

}
