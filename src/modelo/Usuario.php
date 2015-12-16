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

    function __construct($nome, $email, $login, $senha) {
        $this->nome = $nome;
        $this->email = $email;
        $this->login = $login;
        $this->senha = $senha;
    }

    public function getId() {
        return $this->id;
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

    public function getAtivo() {
        return $this->ativo;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }

    public function clonar() {
        $clone = new Usuario("", "", "", "");

        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);
        $clone->setNome($this->nome);
        $clone->setEmail($this->email);
        $clone->setLogin($this->login);
        $clone->setSenha($this->senha);

        return $clone;
    }

}
