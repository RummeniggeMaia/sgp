<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use modelo\Departamento;
use controle\ValidadorDepartamento;
use util\Util;

/**
 * Description of DepartamentoCtrl
 *
 * @author Rummenigge
 */
class DepartamentoCtrl extends Controlador {

    public $validadorDepartamento;

    public function __construct() {
        $this->entidade = new Departamento("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorDepartamento = new ValidadorDepartamento();
    }

    /**
     * Factory method para gerar departamentos baseado a partir do POST
     */
    public function gerarDepartamento($post) {
        if (isset($post['campo_descricao'])) {
            $this->entidade->setDescricao($post['campo_descricao']);
            $this->entidade->setConstante(false);
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarDepartamento($post);
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_departamento');
        $redirecionamento->setCtrl($this);

        if ($funcao == "salvar") {
            $resultado = $this->validadorDepartamento->validarCadastro($this->entidade);
            if ($resultado != null) {
                $this->mensagem = new Mensagem(
                        "Cadastro de departamentos"
                        , "msg_tipo_error"
                        , $resultado);
            } else {
                if ($this->modoEditar) {
                    $this->dao->editar($this->entidade);
                } else {
                    $this->dao->criar($this->entidade);
                }
                $this->entidade = new Departamento("", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de departamentos"
                        , "msg_tipo_ok"
                        , "Dados do Departamento salvo com sucesso.");
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
            $this->entidade = new Departamento("", "");
        } else if ($funcao == 'enviar_departamentos') {
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
            $ctrl->setDepartamentos($selecionados);
            $this->modoBusca = false;
            $redirecionamento->setDestino($this->getCtrlDestino());
            $redirecionamento->setCtrl($controladores[$this->getCtrlDestino()]);
            return $redirecionamento;
        } else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            if ($index != 0) {
                $this->entidade = $this->entidades[$index - 1];
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
            return parent::paginar($funcao, "gerenciar_departamento");
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $departamento) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $departamento->getDescricao();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

}
