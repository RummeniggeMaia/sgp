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

    /** Esse campo é transiente, por isso não tem anotação. 
     * Usado para indicar se essa entidade foi selecionada em uma tabela */
    private $selecionado;

    /**
     * Esse campo é transiente e é utilizado para informar em qual indice de 
     * um drop down essa entidade pertence.
     */
    private $indice;
    
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
    
    public function getIndice() {
        return $this->indice;
    }

    public function setIndice($indice) {
        $this->indice = $indice;
    }

    public abstract function getClassName();

}
