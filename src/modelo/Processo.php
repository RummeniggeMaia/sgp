<?php

namespace modelo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Entidade;
use modelo\Funcionario;
use modelo\Usuario;
use modelo\Protocolo;
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

    /** @ManyToOne(targetEntity="Funcionario", inversedBy="processos", fetch="EAGER") 
     *  @JoinColumn(name="funcionario_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $funcionario;

    /** @ManyToOne(targetEntity="Assunto", inversedBy="processos", fetch="EAGER") 
     *  @JoinColumn(name="assunto_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assunto;

    /** @ManyToOne(targetEntity="Departamento", inversedBy="processos", fetch="EAGER") 
     *  @JoinColumn(name="departamento_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $departamento;

    /** @OneToMany(targetEntity="ProcessoMovimentacao", mappedBy="processo", fetch="EAGER", cascade={"merge", "remove"}) */
    protected $processoMovimentacoes;

    /** @OneToOne(targetEntity="Protocolo", inversedBy="processo", cascade={"merge", "remove"})
     *  @JoinColumn(name="protocolo_id", referencedColumnName="id")
     */
    protected $protocolo;

    /** @ManyToOne(targetEntity="Usuario", inversedBy="processos", fetch="EAGER") 
     *  @JoinColumn(name="usuario_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $usuario;
    private $clonado;

    function __construct($numeroProcesso) {
        $this->numeroProcesso = $numeroProcesso;
        $this->processoMovimentacoes = new ArrayCollection();
        $this->funcionario = new Funcionario("", "", "");
        $this->assunto = new Assunto("", false);
        $this->departamento = new Departamento("", false);
        $this->usuario = new Usuario("", "", "", "");
        $this->protocolo = new Protocolo();
    }

    public function getNumeroProcesso() {
        return $this->numeroProcesso;
    }

    public function getFuncionario() {
        if ($this->funcionario == null) {
            $this->funcionario = new Funcionario("", "", "");
        }
        return $this->funcionario;
    }

    public function getAssunto() {
        if ($this->assunto == null) {
            $this->assunto = new Assunto("", false);
        }
        return $this->assunto;
    }

    public function getDepartamento() {
        if ($this->departamento == null) {
            $this->departamento = new Departamento("", false);
        }
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

    public function getProtocolo() {
        return $this->protocolo;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setProtocolo($protocolo) {
        $this->protocolo = $protocolo;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function getClonado() {
        return $this->clonado;
    }

    function setClonado($clonado) {
        $this->clonado = $clonado;
    }

    public function clonar() {
        $clone = new Processo("");

        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);

        $clone->setNumeroProcesso($this->numeroProcesso);
        $clone->setFuncionario(
                $this->funcionario == null ?
                        new Funcionario("", "", "") :
                        $this->getFuncionario()->clonar());
        $clone->setAssunto(
                $this->assunto == null ?
                        new Assunto("", false) :
                        $this->getAssunto()->clonar());
        $clone->setDepartamento(
                $this->departamento == null ?
                        new Departamento("", false) :
                        $this->getDepartamento()->clonar());

        $pms = new ArrayCollection();
        foreach ($this->processoMovimentacoes->toArray() as $pm) {
            $clonePm = $pm->clonar();
            $clonePm->setProcesso($clone);
            $pms->add($clonePm);
        }
        $clone->setUsuario($this->usuario == null ?
                        new Usuario("", "", "", "") :
                        $this->usuario->clonar());
        if (!$this->clonado) {
            $this->protocolo->setClonado(true);
            $clone->setProtocolo($this->protocolo->clonar());
        }
        $clone->setProcessoMovimentacoes($pms);
        return $clone;
    }

}
