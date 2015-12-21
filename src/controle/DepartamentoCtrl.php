<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorDepartamento;
use DateTime;
use DateTimeZone;
use modelo\Departamento;
use modelo\Log;
use util\Util;

/**
 * Description of DepartamentoCtrl
 *
 * @author Rummenigge
 */
class DepartamentoCtrl extends Controlador {

    private $validadorDepartamento;
    private $post;
    private $controladores;

    public function __construct() {
        $this->descricao = "gerenciar_departamento";
        $this->entidade = new Departamento("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorDepartamento = new ValidadorDepartamento();
    }
    
    public function getValidadorDepartamento() {
        return $this->validadorDepartamento;
    }

    public function setValidadorDepartamento($validadorDepartamento) {
        $this->validadorDepartamento = $validadorDepartamento;
    }
    

    /**
     * Factory method para gerar departamentos baseado a partir do POST
     */
    public function gerarDepartamento() {
        if (isset($this->post['campo_descricao'])) {
            $this->entidade->setDescricao(
                    strtoupper($this->post['campo_descricao']));
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->post = $post;
        $this->controladores = $controladores;

        $this->gerarDepartamento();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_departamento');
        $redirecionamento->setCtrl($this);
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarDepartamento();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarDepartamento();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Departamento("");
        } else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarDepartamento($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirDepartamento($index);
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
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

    private function salvarDepartamento() {
        $this->validadorDepartamento->validar($this->entidade);
        if (!$this->validadorDepartamento->getValido()) {
            $this->mensagem = $this->validadorDepartamento->getMensagem();
            $this->tab = "tab_form";
        } else {
            $this->entidade->setConstante(true);
            $log = new Log();
            if ($this->modoEditar) {
                $log = $this->gerarLog(Log::TIPO_EDICAO);
                $this->dao->editar($this->entidade);
            } else {
                $this->copiaEntidade = $this->dao->editar($this->entidade);
                $log = $this->gerarLog(Log::TIPO_CADASTRO);
            }
            $this->dao->editar($log);
            $this->entidade = new Departamento("", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de departamento"
                    , Mensagem::MSG_TIPO_OK
                    , "Dados do Departamento salvos com sucesso.");
        }
    }

    private function pesquisarDepartamento() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    private function editarDepartamento($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->copiaEntidade = $this->entidade->clonar();
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    private function excluirDepartamento($index) {
        if ($index != 0) {
            $this->copiaEntidade = $this->entidades[$index - 1];
            $this->dao->excluir($this->copiaEntidade);
            $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
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
        $this->validadorDepartamento = new ValidadorDepartamento();
        $this->post = null;
        $this->controladores = null;
    }

    private function gerarLog($tipo) {
        $log = new Log();
        $log->setTipo($tipo);
        $autenticacaoCtrl = $this->controladores["gerenciar_autenticacao"];
        $log->setUsuario($autenticacaoCtrl->getEntidade());
        $log->setDataHora(new DateTime("now", new DateTimeZone('America/Sao_Paulo')));
        $entidade = array();
        $campos = array();
        $entidade["classe"] = $this->copiaEntidade->getClassName();
        $entidade["id"] = $this->copiaEntidade->getId();
        if ($log->getTipo() == Log::TIPO_CADASTRO) {
            $log->setDadosAlterados(json_encode($entidade));
        } else if ($log->getTipo() == Log::TIPO_EDICAO) {
            if ($this->copiaEntidade->getDescricao() !=
                    $this->entidade->getDescricao()) {
                $campos["descricao"] = $this->copiaEntidade->getDescricao();
            }
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        } else if ($log->getTipo() == Log::TIPO_REMOCAO) {
            $campos["descricao"] = $this->copiaEntidade->getDescricao();
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        }
        return $log;
    }

}
