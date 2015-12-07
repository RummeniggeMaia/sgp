<?php

namespace modelo;

use modelo\Entidade;
use modelo\ProcessoMovimentacao;

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

    /** @OneToMany(targetEntity="ProcessoMovimentacao", mappedBy="movimentacao") */
    protected $processoMovimetacoes;

    function __construct($descricao, $constante) {
        $this->constante = $constante;
        $this->descricao = $descricao;
        $this->processoMovimetacoes = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getProcessoMovimetacoes() {
        return $this->processoMovimetacoes;
    }

    public function setProcessoMovimetacoes($processoMovimetacoes) {
        $this->processoMovimetacoes = $processoMovimetacoes;
    }

    public function setConstante($constante) {
        $this->constante = $constante;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

    public function clonar() {
        $clone = new Movimentacao(null, "", false);

        $clone->setId($this->id);
        //$clone->setAtivo($this->ativo);
        $clone->setIndice($this->indice);
        $clone->setSelecionado($this->selecionado);

        $clone->setConstante($this->constante);
        $clone->setDescricao($this->descricao);

        return $clone;
    }

}
