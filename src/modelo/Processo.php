<?php

namespace modelo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Entidade;
use modelo\Funcionario;
use modelo\ProcessoMovimentacao;
use ReflectionClass;

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

    /** @OneToMany(targetEntity="ProcessoMovimentacao", mappedBy="processo", fetch="EAGER", cascade={"merge", "remove"}) */
    protected $processoMovimentacoes;

    function __construct($numeroProcesso) {
        $this->numeroProcesso = $numeroProcesso;
        $this->processoMovimentacoes = new ArrayCollection();
        $this->funcionario = new Funcionario("", "", "");
        $this->assunto = new Assunto("", false);
        $this->departamento = new Departamento("", false);
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

    public function getProcessoMovimentacoes() {
        return $this->processoMovimentacoes;
    }

    public function setProcessoMovimentacoes($processoMovimentacoes) {
        $this->processoMovimentacoes = $processoMovimentacoes;
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

    public function getClassName() {
        $rc = new ReflectionClass($this);
        return $rc->getName();
    }

    public function clonar() {
        $clone = new Processo("");

        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);

        $clone->setNumeroProcesso($this->numeroProcesso);
        $clone->setFuncionario(
                $this->funcionario == null ?
                        new Funcionario("", "", "") :
                        $this->funcionario->clonar());
        $clone->setAssunto(
                $this->assunto == null ?
                        new Assunto("", false) :
                        $this->assunto->clonar());
        $clone->setDepartamento(
                $this->departamento == null ?
                        new Departamento("", false) :
                        $this->departamento->clonar());

        $pms = new ArrayCollection();
        foreach ($this->processoMovimentacoes->toArray() as $pm) {
            $clonePm = $pm->clonar();
            $clonePm->setProcesso($clone);
            $pms->add($clonePm);
        }
        $clone->setProcessoMovimentacoes($pms);
        return $clone;
    }
}
