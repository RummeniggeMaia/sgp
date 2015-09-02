<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use modelo\Assunto;
use controle\ValidadorAssunto;
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
        $this->mensagem = null;


        if ($funcao == "salvar") {
            $resultado = $this->validadorAssunto->validarCadastro($this->entidade);
            if ($resultado != null) {
                $this->mensagem = new Mensagem(
                        "Cadastro de assuntos"
                        , "msg_tipo_error"
                        , $resultado);
            } else {
                if ($this->modoEditar) {
                    $this->dao->editar($this->entidade);
                } else {
                    $this->dao->criar($this->entidade);
                }
                $this->entidade = new Assunto("", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de assuntos"
                        , "msg_tipo_ok"
                        , "Dados do Assunto salvo com sucesso.");
            }
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->entidade));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->entidade);
            $this->pesquisar();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Assunto("", "");
        } else if ($funcao == 'enviar_assunto') {
            foreach ($post as $chave => $valor) {
                if (Util::startsWithString($chave, "check_")) {
                    $index = str_replace("check_", "", $chave);
                    $this->entidades[$index - 1]->setSelecionado(true);
                }
            }
            $selecionados = array();
            foreach ($this->entidades as $f) {
                if ($f->getSelecionado() == true) {
                    $selecionados[] = clone $f;
                }
            }
            $ctrl = $controladores[$this->ctrlDestino];
            $ctrl->setAssuntos($selecionados);
            $this->modoBusca = false;
            $redirecionamento->setDestino($this->getCtrlDestino());
            $redirecionamento->setCtrl($controladores[$this->getCtrlDestino()]);
            return $redirecionamento;
            
        } else if (Util::startsWithString($funcao, "editar_")) {
            $resultado = $this->validadorAssunto->validarEdicao($funcao);
            if ($resultado != 0) {
                $this->entidade = $this->entidades[$resultado - 1];
                $this->modoEditar = true;
            }
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
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
        } else if (Util::startsWithString($funcao, "paginador_")) {
            return parent::paginar($funcao, "gerenciar_assunto");
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

}
