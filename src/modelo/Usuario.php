<?php

namespace modelo;

use modelo\Entidade;

/**
 * Description of Usuario
 *
 * @author Rummenigge
 * @Entity
 * @Table(name="usuario")
 */
class Usuario extends Entidade {

    /** @Column(type="string") */
    protected $nome;
    
    /** @Column(type="string") */
    protected $email;
    
    /** @Column(type="string", unique=true) */
    protected $login;

    /** @Column(type="string") */
    protected $senha;

    function __construct($login, $senha) {
        $this->login = $login;
        $this->senha = $senha;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

    public function clonar() {
        
    }
    
    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setEmail($email) {
        $this->email = $email;
    }
}
