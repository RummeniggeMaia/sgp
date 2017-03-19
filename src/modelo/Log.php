<?php

namespace modelo;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use ReflectionClass;

/**
 * A tabela Log é usada para armazenar os dados modificados recentementes pelos 
 * usuarios. Sempre é armazenado o valor anterior ao atual. Ex: Se uma entidade 
 * tem um campo com valor 1 e editado pra 2, entao o valor 1 é armazenado nessa 
 * tabela.
 *
 * @Entity 
 * @Table(name="log")
 */
class Log extends Entidade {

    /** @ManyToOne(targetEntity="Usuario", inversedBy="logs", cascade={"persist"}) */
    private $usuario;
    /**
     * Os tipos sao CADASTRO, EDICAO, REMOCAO e NULO, 
     */
    /** @Column(type="string") */
    private $tipo;
    /*
     * Dados serão armazenados como uma string em JSON
     */
    /** @Column(type="string") */
    private $dadosAlterados;
    
    /** @Column(type="datetime") */
    private $dataHora;
    
    const TIPO_CADASTRO = "TIPO_CADASTRO";
    const TIPO_EDICAO = "TIPO_EDICAO";
    const TIPO_REMOCAO = "TIPO_REMOCAO";

    public function getUsuario() {
        return $this->usuario;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getDadosAlterados() {
        return $this->dadosAlterados;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
    
    public function setDadosAlterados($dadoAlterados) {
        $this->dadosAlterados = $dadoAlterados;
    }
    
    public function getDataHora() {
        return $this->dataHora;
    }

    public function setDataHora($data) {
        $this->dataHora = $data;
    }

    public function clonar() {
        $clone = new Log();
        
        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);
        
        $clone->setUsuario($this->usuario->clonar());
        $clone->setTipo($this->tipo);
        $clone->setDadosAltarados($this->dadosAltarados);
        
        return $clone;
    }

    public function getClassName() {
        $rc = new ReflectionClass($this);
        return $rc->getName();
    }

}
