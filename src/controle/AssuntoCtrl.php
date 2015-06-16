<?php

namespace controle;

use controle\Controlador;
use dao\Dao;
use modelo\Assunto;

/**
 * Description of AssuntoCtrl
 *
 * @author Rummenigge
 */
class AssuntoCtrl implements Controlador {

    private $assunto;
    private $aux;
    private $assuntos;
    private $dao;

    public function __construct() {
        $this->assunto = new Assunto("", "");
        $this->aux = new Assunto("", "");
        $this->assuntos = array();
    }

    public function getDao() {
        return $this->dao;
    }

    public function setDao($dao) {
        $this->dao = $dao;
    }

    public function getAssunto() {
        return $this->assunto;
    }

    public function getAux() {
        return $this->aux;
    }

    public function setAssunto($assunto) {
        $this->assunto = $assunto;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }

    public function getAssuntos() {
        return $this->assuntos;
    }

    /**
     * Factory method para gerar assuntos baseado a partir do POST
     */
    public function gerarAssunto($post) {
        if (isset($post['campo_constante'])) {
            $this->assunto->setConstante($post['campo_constante']);
        }
        if (isset($post['campo_descricao'])) {
            $this->assunto->setDescricao($post['campo_descricao']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarAssunto($post);
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->assunto);
            $this->assunto = new Assunto("", "");
        }else{
            return false;
        }
    }

}
