<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorFuncionario;
use DateTime;
use DateTimeZone;
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
        $this->descricao = "gerenciar_funcionario";
        $this->entidade = new Funcionario("", "", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Nome", "RG", "CPF"));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorFuncionario = new ValidadorFuncionario();
    }

    /**
     * Factory method para gerar funcionarios baseado a partir do POST
     */
    public function gerarFuncionario() {
        if (isset($this->post['campo_nome'])) {
            $this->entidade->setNome($this->post['campo_nome']);
        }
        if (isset($this->post['campo_cpf'])) {
            $this->entidade->setCpf($this->post['campo_cpf']);
        }
        if (isset($this->post['campo_rg'])) {
            $this->entidade->setRg($this->post['campo_rg']);
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->post = $post;
        $this->controladores = $controladores;

        $this->gerarFuncionario();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_funcionario');
        $redirecionamento->setCtrl($this);
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarFuncionario();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarFuncionario();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Funcionario("", "", "");
        } else if ($funcao == 'enviar_funcionarios') {
            return $this->enviarFuncionarios();
        } else if ($funcao == 'cancelar_enviar') {
            $this->setCtrlDestino("");
            $this->setModoBusca(false);
        } else if (Util::startsWithString($funcao, "editar_")) {
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
        $linhas = array();
        foreach ($this->entidades as $funcionario) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $funcionario->getNome();
            $valores[] = $funcionario->getRg();
            $valores[] = $funcionario->getCpf();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarFuncionario() {
        $this->validadorFuncionario->validar($this->entidade);
        if (!$this->validadorFuncionario->getValido()) {
            $this->mensagem = $this->validadorFuncionario->getMensagem();
            $this->tab = "tab_form";
        } else {
            $log = new Log();
            if ($this->modoEditar) {
                $log = $this->gerarLog(Log::TIPO_EDICAO);
                $this->dao->editar($this->entidade);
            } else {
                $this->copiaEntidade = $this->dao->editar($this->entidade);
                $log = $this->gerarLog(Log::TIPO_CADASTRO);
            }
            $this->dao->editar($log);
            $this->entidade = new Funcionario("", "", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , Mensagem::MSG_TIPO_OK
                    , "Dados do Funcionário salvos com sucesso.");
        }
    }

    private function pesquisarFuncionario() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    private function enviarFuncionarios() {
        $redirecionamento = new Funcionario();
        $selecionados = array();
        foreach ($this->post as $valor) {
            if (Util::startsWithString($valor, "radio_")) {
                $index = str_replace("radio_", "", $valor);
                $selecionados[] = clone $this->entidades[$index - 1];
            }
        }
        $ctrl = $this->controladores[$this->ctrlDestino];
        $ctrl->setFuncionarios($selecionados);
        $this->modoBusca = false;
        $redirecionamento->setDestino($this->getCtrlDestino());
        $redirecionamento->setCtrl($this->controladores[$this->getCtrlDestino()]);
        return $redirecionamento;
    }

    private function editarFuncionario($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->copiaEntidade = $this->entidade->clonar();
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    private function excluirFuncionario($index) {
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
        $this->validadorFuncionario = new ValidadorFuncionario();
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
    }

}
