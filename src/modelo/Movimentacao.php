<?php

namespace modelo;

use modelo\Entidade;
use modelo\Processo;

/**
 *
 * @author Rummenigge
 * @Entity
 * @Table(name="movimentacao")
 */
class Movimentacao extends Entidade {

    /** @Column(type="boolean") */
    protected $constante;

    /** @Column(type="string") */
    protected $descricao;

    /** @Column(type="date") */
    protected $dataProcesso;

    /**
     * @ManyToMany(targetEntity="Processo", inversedBy="movimentacoes")
     * @JoinTable(name="processos_movimentacoes")     
     */
    protected $processos;

    function __construct($dataProcesso, $descricao, $constante) {
        $this->constante = $constante;
        $this->descricao = $descricao;
        $this->dataProcesso = $dataProcesso;
        $this->processos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
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

    public function getDataProcesso() {
        return $this->dataProcesso;
    }

    public function setDataProcesso($dataProcesso) {
        $this->dataProcesso = $dataProcesso;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

}
