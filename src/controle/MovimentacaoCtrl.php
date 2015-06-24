<?php

namespace controle;

use controle\Controlador;
use dao\Dao;
use modelo\Movimentacao;
use controle\Mensagem;

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
    private $mensagem;

    public function __construct() {
        $this->movimentacao = new Movimentacao("", "");
        $this->aux = new Movimentacao("", "");
        $this->movimentacaos = array();
        $mensagem = false;
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
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

    public function getMovimentacaos() {
        return $this->movimentacaos;
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
            $this->mensagem = new Mensagem(
                    "Cadastro de Movimentações"
                    , "msg_tipo_ok"
                    , "Movimentação cadastrada com sucesso.");
            return 'gerenciar_movimentacao';
        } else {
            return false;
        }
    }

}
