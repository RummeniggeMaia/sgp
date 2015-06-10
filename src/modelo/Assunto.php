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

    /** @Column(type="string") */
    protected $descricao;

    /** @OneToMany(targetEntity="Processo", mappedBy="assunto") */
    protected $processos;

    function __construct($constante, $descricao) {
        parent::__construct();
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
}
