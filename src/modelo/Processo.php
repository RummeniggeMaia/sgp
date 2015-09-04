<?php

namespace modelo;

use modelo\Entidade;
use modelo\Funcionario;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Movimentacao;

/**
 *
 * @author Rummenigge
 * @Entity
 * @Table(name="processo")
 */
class Processo extends Entidade {

    /** @Column(type="string", unique=true) */
    protected $numeroProcesso;

    /** @ManyToOne(targetEntity="Funcionario", inversedBy="processos") */
    protected $funcionario;

    /** @ManyToOne(targetEntity="Assunto", inversedBy="processos") */
    protected $assunto;

    /** @ManyToOne(targetEntity="Departamento", inversedBy="processos") */
    protected $departamento;

    /** @ManyToMany(targetEntity="Movimentacao", mappedBy="processos") */
    protected $movimentacoes;

    function __construct($numeroProcesso) {
        $this->numeroProcesso = $numeroProcesso;
        $this->movimentacoes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getNumeroProcesso() {
        return $this->numeroProcesso;
    }

    public function getFuncionario() {
        return $this->funcionario;
    }

    public function getAssunto() {
        return $this->assunto;
    }

    public function getDepartamento() {
        return $this->departamento;
    }

    public function getMovimentacoes() {
        return $this->movimentacoes;
    }

    public function setNumeroProcesso($numeroProcesso) {
        $this->numeroProcesso = $numeroProcesso;
    }

    public function setFuncionario($funcionario) {
        $this->funcionario = $funcionario;
    }

    public function setAssunto($assunto) {
        $this->assunto = $assunto;
    }

    public function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }

    public function setMovimentacoes($movimentacoes) {
        $this->movimentacoes = $movimentacoes;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

}
