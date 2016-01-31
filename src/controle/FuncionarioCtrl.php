<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorFuncionario;
use dao\Dao;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use modelo\Funcionario;
use modelo\Log;
use util\Util;

/**
 * Description of FuncionarioCtrl
 *
 * @author Rummenigge
 */
class FuncionarioCtrl extends Controlador {

    private $validadorFuncionario;
    private $controladores;
    private $post;

    public function __construct() {
        $this->descricao = Controlador::CTRL_FUNCIONARIO;
        $this->entidade = new Funcionario("", "", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Nome", "RG", "CPF"));
        $this->modeloTabela->getPaginador()->setPesquisa(new Funcionario("", "", ""));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorFuncionario = new ValidadorFuncionario();
    }

    public function getValidadorFuncionario() {
        return $this->validadorFuncionario;
    }

    public function setValidadorFuncionario($validadorFuncionario) {
        $this->validadorFuncionario = $validadorFuncionario;
    }

    /**
     * Factory method para gerar funcionarios baseado a partir do POST
     */
    public function gerarFuncionario() {
        if (isset($this->post['campo_nome'])) {
            $this->entidade->setNome(trim($this->post['campo_nome']));
        }
        if (isset($this->post['campo_cpf'])) {
            $this->entidade->setCpf($this->post['campo_cpf']);
        }
        if (isset($this->post['campo_rg'])) {
            $this->entidade->setRg($this->post['campo_rg']);
        }
    }

    public function executarFuncao($post, $funcao, & $controladores) {
        $this->post = $post;
        $this->controladores = &$controladores;

        $this->gerarFuncionario();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_FUNCIONARIO);
        $redirecionamento->setCtrl($this);
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarFuncionario();
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->
                    setPesquisa($this->entidade->clonar());
            $this->pesquisarFuncionario();
        } else if ($funcao == "cancelar_edicao") {
            $this->tab = "tab_form";
            $this->modoEditar = false;
            $this->entidade = new Funcionario("", "", "");
        } else if ($funcao == 'enviar_funcionarios') {
            return $this->enviarFuncionarios();
        } else if ($funcao == 'cancelar_enviar') {
            $this->setCtrlDestino("");
            $this->setModoBusca(false);
        } else if (Util::startsWithString($funcao, "editar_")) {
            $this->tab = "tab_form";
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarFuncionario($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirFuncionario($index);
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $autCtrl = $this->controladores[Controlador::CTRL_AUTENTICACAO];
        $admin = $autCtrl->contemAutorizacao("admin");
        $linhas = array();
        foreach ($this->entidades as $funcionario) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $funcionario->getNome();
            if ($admin) {
                $valores[] = $funcionario->getRg();
                $valores[] = $funcionario->getCpf();
            } else {
                $valores[] = "***.***.***";
                $valores[] = "***." . substr($funcionario->getCpf(), 4, 12) . "-**";
            }
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarFuncionario() {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $this->validadorFuncionario->validar($this->entidade);
        if (!$this->validadorFuncionario->getValido()) {
            $this->mensagem = $this->validadorFuncionario->getMensagem();
            $this->tab = "tab_form";
        } else {
            try {
                $log = new Log();
                if ($this->modoEditar) {
                    $this->copiaEntidade = $this->dao->pesquisarPorId($this->entidade);
                    if ($this->copiaEntidade == null) {
                        throw new Exception("Entidade inexistente, não é possível editá-la.");
                    }
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
                $this->dao->editar($log);
                $this->entidade = new Funcionario("", "", "");
                $this->copiaEntidade = new Funcionario("", "", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de funcionários"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados do Funcionário salvos com sucesso.");
            } catch (UniqueConstraintViolationException $ex) {
                $this->validadorFuncionario->setValido(false);
                $this->validadorFuncionario->setCamposInvalidos(array("campo_cpf", "campo_rg"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "O CPF ou o RG já existe cadastrado no sistema.\n");
            } catch (Exception $e) {
                $this->mensagem = new Mensagem(
                        "Cadastro de funcionários"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro ao salvar o funcionário");
            }
        }
    }

    private function pesquisarFuncionario() {
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->modeloTabela->
                                getPaginador()->getPesquisa()));
        $this->pesquisar();
    }

    private function enviarFuncionarios() {
        $redirecionamento = new Redirecionamento();
        $selecionados = array();
        foreach ($this->post as $valor) {
            if (Util::startsWithString($valor, "radio_")) {
                $index = str_replace("radio_", "", $valor);
                if (isset($this->entidades[$index - 1])) {
                    $selecionados[] = $this->entidades[$index - 1]->clonar();
                }
            }
        }
        $ctrl = $this->controladores[$this->ctrlDestino];
        $ctrl->setFuncionarios($selecionados);
        $ctrl->setDao(new Dao($this->dao->getEntityManager()));
        $this->modoBusca = false;
        $this->entidades = array();
        $this->modeloTabela->setLinhas(array());
        $redirecionamento->setDestino($this->getCtrlDestino());
        $redirecionamento->setCtrl($this->controladores[$this->getCtrlDestino()]);
        return $redirecionamento;
    }

    private function editarFuncionario($index) {
        if ($index > 0 && $index <= count($this->entidades)) {
            $aux = $this->dao->pesquisarPorId($this->entidades[$index - 1]);
            if ($aux == null) {
                $this->entidade = new Funcionario("", "", "");
            } else {
                $this->entidade = $aux;
                $this->copiaEntidade = $this->entidade->clonar();
                $this->modoEditar = true;
            }
        }
    }

    private function excluirFuncionario($index) {
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
                        "Remover funcionário"
                        , Mensagem::MSG_TIPO_OK
                        , "Funcionário removido com sucesso.");
            } else {
                $this->mensagem = new Mensagem(
                        "Remover funcionário"
                        , Mensagem::MSG_TIPO_AVISO
                        , "Funcionário já foi removido por outro usuário.");
            }
        }
    }
    
    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Funcionario("", "", "");
            } else {
                $this->entidade = $aux;
            }
        }
        $this->pesquisarFuncionario();
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorFuncionario = new ValidadorFuncionario();
        $this->post = null;
        $this->dao = null;
        $this->copiaEntidade = new Funcionario("", "", "");
    }

    private function gerarLog($tipo) {
        try {
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
                if ($this->copiaEntidade->getNome() != $this->entidade->getNome()) {
                    $campos["nome"] = $this->copiaEntidade->getNome();
                }
                if ($this->copiaEntidade->getCpf() != $this->entidade->getCpf()) {
                    $campos["cpf"] = $this->copiaEntidade->getCpf();
                }
                if ($this->copiaEntidade->getRg() != $this->entidade->getRg()) {
                    $campos["rg"] = $this->copiaEntidade->getRg();
                }
                $entidade["campos"] = $campos;
                $log->setDadosAlterados(json_encode($entidade));
            } else if ($log->getTipo() == Log::TIPO_REMOCAO) {
                $campos["nome"] = $this->copiaEntidade->getNome();
                $campos["rg"] = $this->copiaEntidade->getRg();
                $campos["cpf"] = $this->copiaEntidade->getCpf();
                $entidade["campos"] = $campos;
                $log->setDadosAlterados(json_encode($entidade));
            }
            return $log;
        } catch (Exception $e) {
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , Mensagem::MSG_TIPO_ERRO
                    , "Erro durante a geração do Log.");
        }
    }
}
