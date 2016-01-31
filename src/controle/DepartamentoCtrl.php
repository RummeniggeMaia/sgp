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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;

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
        $this->descricao = Controlador::CTRL_DEPARTAMENTO;
        $this->entidade = new Departamento("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->getPaginador()->setPesquisa(new Departamento(""));
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
                    trim(strtoupper($this->post['campo_descricao'])));
        }
    }

    public function executarFuncao($post, $funcao, & $controladores) {
        $this->post = $post;
        $this->controladores = &$controladores;

        $this->gerarDepartamento();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_DEPARTAMENTO);
        $redirecionamento->setCtrl($this);
        
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarDepartamento();
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->
                    setPesquisa($this->entidade->clonar());
            $this->pesquisarDepartamento();
        } else if ($funcao == "cancelar_edicao") {
            $this->tab = "tab_form";
            $this->modoEditar = false;
            $this->entidade = new Departamento("");
        } else if (Util::startsWithString($funcao, "editar_")) {
            $this->tab = "tab_form";
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
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $this->validadorDepartamento->validar($this->entidade);
        if (!$this->validadorDepartamento->getValido()) {
            $this->mensagem = $this->validadorDepartamento->getMensagem();
            $this->tab = "tab_form";
        } else {
            try {
                $this->entidade->setConstante(true);
                $log = new Log();
                if ($this->modoEditar) {
                    $this->copiaEntidade = $this->dao->pesquisarPorId($this->entidade);
                    if ($this->copiaEntidade == null) {
                        throw new Exception("Entidade inexistente, não é possível editá-la.");
                    }
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->dao->editar($this->entidade);
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
                $this->dao->editar($log);
                $this->entidade = new Departamento("", "");
                $this->copiaEntidade = new Departamento("");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de departamento"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados do Departamento salvos com sucesso.");
            } catch (UniqueConstraintViolationException $e) {
                $this->validadorDepartamento->setValido(false);
                $this->validadorDepartamento->setCamposInvalidos(array("campo_descricao"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Já existe um departamento com essa descrição.\n");
            } catch (Exception $e) {
                $this->mensagem = new Mensagem(
                        "Cadastro de departamento"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro ao salvar o departamento.");
            }
        }
    }

    private function pesquisarDepartamento() {
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->modeloTabela->
                                getPaginador()->getPesquisa()));
        $this->pesquisar();
    }

    public function editarDepartamento($index) {
        if ($index > 0 && $index <= count($this->entidades)) {
            $aux = $this->dao->pesquisarPorId($this->entidades[$index - 1]);
            if ($aux == null) {
                $this->entidade = new Departamento("");
            } else {
                $this->entidade = $aux;
                $this->copiaEntidade = $this->entidade->clonar();
                $this->modoEditar = true;
            }
        }
    }

    private function excluirDepartamento($index) {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        if ($index != 0) {
            $this->copiaEntidade = $this->dao->pesquisarPorId(
                    $this->entidades[$index - 1]);
            if ($this->copiaEntidade != null) {
                $this->dao->excluir($this->copiaEntidade);
                $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
                $this->mensagem = new Mensagem(
                        "Remover departamento"
                        , Mensagem::MSG_TIPO_OK
                        , "Departamento removido com sucesso.");
            } else {
                $this->mensagem = new Mensagem(
                        "Remover departamento"
                        , Mensagem::MSG_TIPO_AVISO
                        , "Departamento já foi removido por outro usuário.");
            }
        }
    }

    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Departamento("");
            } else {
                $this->entidade = $aux;
            }
        }
        $this->pesquisarDepartamento();
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorDepartamento = new ValidadorDepartamento();
        $this->post = null;
        $this->dao = null;
        $this->copiaEntidade = new Departamento("");
    }

    private function gerarLog($tipo) {
        $log = new Log();
        $log->setTipo($tipo);
        $autenticacaoCtrl = $this->controladores[Controlador::CTRL_AUTENTICACAO];
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
