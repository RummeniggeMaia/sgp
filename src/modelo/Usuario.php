<?php

namespace modelo;

use modelo\Entidade;
use modelo\Log;
use modelo\Processo;

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

    /** @OneToMany(targetEntity="Log", mappedBy="usuario", cascade={"remove"}) */
    protected $logs;

    /** @ManyToMany(targetEntity="Autorizacao", fetch="EAGER") 
     *  @JoinTable(name="usuario_autorizacao")
     */
    protected $autorizacoes;
    
    /** @OneToMany(targetEntity="Processo", mappedBy="usuario") */
    protected $processos;
    
    private $autenticar;

    function __construct($nome, $email, $login, $senha) {
        $this->autorizacoes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->nome = $nome;
        $this->email = $email;
        $this->login = $login;
        $this->senha = $senha;
        $this->autenticar = false;
        $this->processos = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getLogs() {
        return $this->logs;
    }

    public function setLogs($logs) {
        $this->logs = $logs;
    }

    public function setAutorizacoes($autorizacoes) {
        $this->autorizacoes = $autorizacoes;
    }

    public function getAutorizacoes() {
        return $this->autorizacoes;
    }
    
    public function getAutenticar() {
        return $this->autenticar;
    }

    public function setAutenticar($autenticar) {
        $this->autenticar = $autenticar;
    }
    function getProcessos() {
        return $this->processos;
    }

    function setProcessos($processos) {
        $this->processos = $processos;
    }

    public function clonar() {
        $clone = new Usuario("", "", "", "");

        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);
        $clone->setNome($this->nome);
        $clone->setEmail($this->email);
        $clone->setLogin($this->login);
        $clone->setSenha($this->senha);
        $auts = array();
        foreach ($this->autorizacoes as $a) {
            $auts[] = $a->clonar();
        }
        $clone->setAutorizacoes($auts);
        return $clone;
    }
}
