<?php

use modelo\Entidade;
use modelo\Processo;

namespace modelo;

/**
 *
 *
 * @Entity 
 * @Table(name="funcionario")
 */
class Funcionario extends Entidade {

    /** @Column(type="string") */
    protected $nome = "teste";

    /** @Column(type="string") */
    protected $cpf;

    /** @Column(type="string") */
    protected $rg;

    /** @OneToMany(targetEntity="Processo", mappedBy="funcionario") */
    protected $processos;

    function __construct($nome, $cpf, $rg) {
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->rg = $rg;
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

    public function getNome() {
        return $this->nome;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function getRg() {
        return $this->rg;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    public function setRg($rg) {
        $this->rg = $rg;
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
        $clone = new Funcionario("", "", "");
        
        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);
        
        $clone->setNome($this->nome);
        $clone->setCpf($this->cpf);
        $clone->setRg($this->rg);
        
        return $clone;
    }

}
