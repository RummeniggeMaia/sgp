<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use modelo\Funcionario;
use util\Util;

/**
 * Description of FuncionarioCtrl
 *
 * @author Rummenigge
 */
class FuncionarioCtrl extends Controlador {

    public function __construct() {
        $this->entidade = new Funcionario("", "", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Nome", "RG", "CPF"));
        $this->modeloTabela->setModoBusca(false);
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

    public function executarFuncao($post, $funcao) {
        $this->gerarFuncionario($post);

        if ($funcao == "salvar") {
            if ($this->modoEditar) {
                $this->dao->editar($this->entidade);
            } else {
                $this->dao->criar($this->entidade);
            }
            $this->entidade = new Funcionario("", "", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , "msg_tipo_ok"
                    , "Dados do Funcionário salvo com sucesso.");
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->entidade));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->entidade);
            $this->pesquisar();      
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Funcionario("", "", "");
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
            return parent::paginar($funcao);
        }
        return 'gerenciar_funcionario';
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

    /* Falta resolver o problema da mascara, pois a mascara tem q ser tirada antes da verificação, para conferir se
      o CPF e RG é válido e são numéricos realmente. */

    private function validacao() {
        if ($this->entidade->getNome() == null || is_numeric($this->entidade->getNome())) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo nome não pode ser vazio ou numérico");
            return 'validacao_erro';
        }
        if ($this->entidade->getRg() == null || is_numeric($this->entidade->getRg())) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo RG não pode ser vazio");
            return 'validacao_erro';
        }
        if ($this->entidade->getCpf() == null || is_numeric($this->entidade->getCpf())) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo CPF não pode ser vazio");
            return 'validacao_erro';
        }
    }

}
