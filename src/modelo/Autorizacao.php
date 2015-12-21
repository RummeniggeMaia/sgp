<?php

namespace modelo;

use modelo\Entidade;

/**
 * Description of Usuario
 *
 * @author Jaedson
 * @Entity
 * @Table(name="autorizacao")
 */
class Autorizacao extends Entidade {

    /** @Column(type="string") */
    protected $descricao;
    /** @ManyToMany(targetEntity="usuario", mappedBy="autorizacoes") */
    protected $usuarios;

    function __construct($descricao){
        $this->descricao = $descricao;
        $this->usuarios = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }  

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }
    
   public function getUsuarios(){
       return $this->usuarios;
   }
   
   public function setUsuarios($usuario){
       $this->usuarios = $usuario;
   }
   
    public function clonar() {
        $clone = new Autorizacao("");

        $clone->setId($this->id);
        $clone->setDescricao($this->descricao);

        return $clone;
    }

}
