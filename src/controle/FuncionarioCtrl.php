<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorFuncionario;
use modelo\Funcionario;
use util\Util;

/**
 * Description of FuncionarioCtrl
 *
 * @author Rummenigge
 */
class FuncionarioCtrl extends Controlador {

    public $validadorFuncionario;

    public function __construct() {
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
    public function gerarFuncionario($post) {
        if (isset($post['campo_nome'])) {
            $this->entidade->setNome($post['campo_nome']);
        }
        if (isset($post['campo_cpf'])) {
            $this->entidade->setCpf($post['campo_cpf']);
        }
        if (isset($post['campo_rg'])) {
            $this->entidade->setRg($post['campo_rg']);
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarFuncionario($post);

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
            return $this->enviarFuncionarios($post, $controladores);
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
            $this->dao->editar($this->entidade);
            $this->entidade = new Funcionario("", "", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , Mensagem::MSG_TIPO_OK
                    , "Dados do Funcionário salvo com sucesso.");
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

    private function enviarFuncionarios($post, $controladores) {
        $redirecionamento = new Funcionario();
        $selecionados = array();
        foreach ($post as $valor) {
            if (Util::startsWithString($valor, "radio_")) {
                $index = str_replace("radio_", "", $valor);
                $selecionados[] = clone $this->entidades[$index - 1];
            }
        }
        $ctrl = $controladores[$this->ctrlDestino];
        $ctrl->setFuncionarios($selecionados);
        $this->modoBusca = false;
        $redirecionamento->setDestino($this->getCtrlDestino());
        $redirecionamento->setCtrl($controladores[$this->getCtrlDestino()]);
        return $redirecionamento;
    }

    private function editarFuncionario($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    private function excluirFuncionario($index) {
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
        $this->validadorFuncionario = new ValidadorFuncionario();
    }

}
