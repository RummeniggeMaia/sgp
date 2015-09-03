<?php

use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;

namespace modelo;

/**
 *
 * @author Rummenigge
 * @MappedSuperclass
 */
abstract class Entidade {

    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(type="boolean") */
    protected $ativo = true;

    /** Esse campo é transiente, por isso não tem anotação */
    private $selecionado;

    public function getId() {
        return $this->id;
    }

    public function getAtivo() {
        return $this->ativo;
    }

    public function getSelecionado() {
        return $this->selecionado;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

    public function setSelecionado($selecionado) {
        $this->selecionado = $selecionado;
    }

    public abstract function getClassName();

}
