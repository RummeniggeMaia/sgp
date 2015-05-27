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

    /**
     * @ManyToMany(targetEntity="Processo", inversedBy="movimentacoes")
     * @JoinTable(name="processos_movimentacoes")     
     */
    protected $processos;

    function __construct($constante, $descricao) {
        parent::__construct();
        $this->constante = $constante;
        $this->descricao = $descricao;
        $this->processos = new ArrayCollection();
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
}
