<?php

namespace controle;

use controle\Controlador;
use dao\Dao;
use modelo\Movimentacao;

/**
 * Description of MovimentacaoCtrl
 *
 * @author Rummenigge
 */
class MovimentacaoCtrl implements Controlador {

    private $movimentacao;
    private $aux;
    private $movimentacaos;
    private $dao;

    public function __construct() {
        $this->movimentacao = new Movimentacao("", "");
        $this->aux = new Movimentacao("", "");
        $this->movimentacaos = array();
    }

    public function getDao() {
        return $this->dao;
    }

    public function setDao($dao) {
        $this->dao = $dao;
    }

    public function getMovimentacao() {
        return $this->movimentacao;
    }

    public function getAux() {
        return $this->aux;
    }

    public function setMovimentacao($movimentacao) {
        $this->movimentacao = $movimentacao;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }

    public function adicionarMovimentacao($movimentacao) {
        $this->$movimentacaos[$movimentacao->getId()] = $movimentacao;
    }

    public function removerMovimentacao($movimentacao) {
        $this->dao.excluir($this->movimentacao);
    }

    /**
     * Factory method para gerar movimentacões baseado a partir do POST
     */
    public function gerarMovimentacao($post) {
        if (isset($post['campo_descricao'])) {
            $this->movimentacao->setDescricao($post['campo_descricao']);
        }
        if (isset($post['campo_constante'])) {
            $this->movimentacao->setConstante($post['campo_constante']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarMovimentatcao($post);
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->movimentacao);
            $this->movimentacao = new Movimentacao("", "");
        }
    }

}