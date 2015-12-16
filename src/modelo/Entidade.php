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

    /** Esse campo é transiente, por isso não tem anotação. 
     * Usado para indicar se essa entidade foi selecionada em uma tabela */
    protected $selecionado;

    public function getId() {
        return $this->id;
    }

    public function getSelecionado() {
        return $this->selecionado;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

    public function setSelecionado($selecionado) {
        $this->selecionado = $selecionado;
    }

    public abstract function getClassName();

    public abstract function clonar();
}
