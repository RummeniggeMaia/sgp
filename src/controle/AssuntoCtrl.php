<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use modelo\Assunto;
use controle\validadores\ValidadorAssunto;
use util\Util;

/**
 * Description of AssuntoCtrl
 *
 * @author Rummenigge
 */
class AssuntoCtrl extends Controlador {

    public $validadorAssunto;

    public function __construct() {
        $this->entidade = new Assunto("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela;
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorAssunto = new ValidadorAssunto();
    }

    // modificado

    /**
     * Factory method para gerar assuntos baseado a partir do POST
     */
    public function gerarAssunto($post) {
        if (isset($post['campo_descricao'])) {
            $this->entidade->setDescricao($post['campo_descricao']);
            $this->entidade->setConstante(false);
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarAssunto($post);

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_assunto');
        $redirecionamento->setCtrl($this);

        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarAssunto();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarAssunto();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Assunto("", "");
        } /* else if ($funcao == 'enviar_assuntos') {
          return $this->enviarAssuntos();
          } else if ($funcao == 'cancelar_enviar') {
          $this->setCtrlDestino("");
          $this->setModoBusca(false);
          } */ else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarAssunto($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirAssunto();
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $assunto) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $assunto->getDescricao();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarAssunto() {
        $resultado = $this->validadorAssunto->validarCadastro($this->entidade);
        if ($resultado != null) {
            $this->mensagem = new Mensagem(
                    "Cadastro de assuntos"
                    , "msg_tipo_error"
                    , $resultado);
        } else {
            $this->entidade->setConstante(true);
            $this->dao->editar($this->entidade);
            $this->entidade = new Assunto("", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de assuntos"
                    , "msg_tipo_ok"
                    , "Dados do Assunto salvo com sucesso.");
        }
    }

    public function pesquisarAssunto() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    /* public function enviarAssuntos($post, $controladores) {
      $redirecionamento = new Redirecionamento();
      $selecionados = array();
      foreach ($post as $valor) {
      if (Util::startsWithString($valor, "radio_")) {
      $index = str_replace("radio_", "", $valor);
      $selecionados[] = clone $this->entidades[$index - 1];
      }
      }
      $ctrl = $controladores[$this->ctrlDestino];
      $ctrl->setAssuntos($selecionados);
      $this->modoBusca = false;
      $redirecionamento->setDestino($this->getCtrlDestino());
      $redirecionamento->setCtrl($controladores[$this->getCtrlDestino()]);
      return $redirecionamento;
      } */

    public function editarAssunto($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    public function excluirAssunto($index) {
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
    }

}
