<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use modelo\Assunto;
use util\Util;

/**
 * Description of AssuntoCtrl
 *
 * @author Rummenigge
 */
class AssuntoCtrl extends Controlador {

    public function __construct() {
        $this->assunto = new Assunto("");
        $this->aux = new Assunto("");
        $this->assuntos = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela;
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->setModoBusca(false);
    }

    /**
     * Factory method para gerar assuntos baseado a partir do POST
     */
    public function gerarAssunto($post) {
        if (isset($post['campo_descricao'])) {
            $this->assunto->setDescricao($post['campo_descricao']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarAssunto($post);
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->assunto);
            $this->assunto = new Assunto("");
            $this->mensagem = new Mensagem(
                    "Cadastro de Assuntos"
                    , "msg_tipo_ok"
                    , "Assunto cadastrado com sucesso.");
            return 'gerenciar_assunto';
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->assunto));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->assunto);
            $this->pesquisar();
            $this->gerarLinhas();
            return 'gerenciar_assunto';
            return false;
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        } else {
            return false;
        }
    }

    private function gerarLinhas() {
        $linhas = array();
        foreach ($this->assuntos as $assunto) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $assunto->getDescricao();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

}
