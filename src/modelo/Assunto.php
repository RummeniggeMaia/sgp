<?php

use modelo\Entidade;
use modelo\Processo;

namespace modelo;

/**
 *
 * @author Rummenigge
 * @Entity
 * @Table(name="assunto")
 */
class Assunto extends Entidade {

    /** @Column(type="boolean") */
    protected $constante;

    /** @Column(type="string", unique=true) */
    protected $descricao;

    /** @OneToMany(targetEntity="Processo", mappedBy="assunto") */
    protected $processos;

    function __construct($descricao, $constante) {
        $this->descricao = $descricao;
        $this->constante = $constante;
        $this->processos = new \Doctrine\Common\Collections\ArrayCollection();
        $rc = new \ReflectionClass($this);
        $this->className = $rc->getName();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
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

}
