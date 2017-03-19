<?php

namespace modelo;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use modelo\Processo;
use ReflectionClass;
use Symfony\Component\Console\Helper\Table;

/**
 *
 *
 * @Entity 
 * @Table(name="protocolo")
 * @autor Rummenigge
 */
class Protocolo extends Entidade {

    /** @Column(type="datetime") */
    protected $dataHora;

    /** @Column(type="string", unique=true) */
    protected $numero;

    /**
     * @OneToOne(targetEntity="Processo", mappedBy="protocolo")
     */
    protected $processo;

    private $clonado;
    
    function __construct() {
        $this->dataHora = new DateTime("now", new DateTimeZone("America/Sao_Paulo"));
    }

    public function setDataHora($dataHora) {
        $this->dataHora = $dataHora;
    }

    public function getDataHora() {
        return $this->dataHora;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getProcesso() {
        return $this->processo;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setProcesso($processo) {
        $this->processo = $processo;
    }
    function getClonado() {
        return $this->clonado;
    }

    function setClonado($clonado) {
        $this->clonado = $clonado;
    }

    public function clonar() {
        $clone = new Protocolo();
        $clone->setId($this->id);
        $clone->setNumero($this->numero);
        $clone->setDataHora($this->dataHora);
        if (!$this->clonado) {
            $this->processo->setClonado(true);
            $clone->setProcesso($this->processo->clonar());
        }
        return $clone;
    }

    public function getClassName() {
        $rc = new ReflectionClass($this);
        return $rc->getName();
    }

}
