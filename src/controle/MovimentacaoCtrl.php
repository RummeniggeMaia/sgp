<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use modelo\Movimentacao;
use util\Util;
use controle\validadores\ValidadorMovimentacao;

/**
 * Description of MovimentacaoCtrl
 *
 * @author Rummenigge
 */
class MovimentacaoCtrl extends Controlador {

    public $validadorMovimentacao;

    public function __construct() {
        $this->entidade = new Movimentacao("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorMovimentacao = new ValidadorMovimentacao();
    }

    /**
     * Factory method para gerar movimentacões baseado a partir do POST
     */
    public function gerarMovimentacao($post) {
        if (isset($post['campo_descricao'])) {
            $this->entidade->setDescricao($post['campo_descricao']);
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarMovimentacao($post);

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_movimentacao');
        $redirecionamento->setCtrl($this);

        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarMovimentacao();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarMovimentacao();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Movimentacao("", "");
        } else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarMovimentacao($index);

        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirMovimentacao();
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $movimentacao) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $movimentacao->getDescricao();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarMovimentacao() {
        $this->validadorMovimentacao->validar($this->entidade);
        if (!$this->validadorMovimentacao->getValido()) {
            $this->mensagem = $this->validadorMovimentacao->getMensagem();
            $this->tab = "tab_form";
        } else {
            $this->dao->editar($this->entidade);
            $this->entidade = new Movimentacao("", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de movimentação"
                    , Mensagem::MSG_TIPO_OK
                    , "Dados de Movimentação salvo com sucesso.");
        }
    }

    private function pesquisarMovimentacao() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    private function editarMovimentacao($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    private function excluirMovimentacao($index) {

        if ($index != 0) {
            $aux = $this->entidades[$index - 1];
            $this->dao->excluir($aux);
            $p = $this->modeloTabela->getPaginador();
            if ($p->getOffset() == $p->getContagem()) {
                $p->anterior();
            }
            $p->setContagem($p->getContagem() - 1);
            $this->pesquisar();
        }
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorMovimentacao = new ValidadorMovimentacao();
    }

}
