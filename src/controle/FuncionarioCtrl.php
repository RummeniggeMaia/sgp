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
        $this->aux = new Funcionario("", "", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela;
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

        if ($funcao == "cadastrar") {
            $this->dao->criar($this->entidade);
            $this->entidade = new Funcionario("", "", "");
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , "msg_tipo_ok"
                    , "Funcionário cadastrado com sucesso.");
            return 'gerenciar_funcionario';
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->entidade));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->entidade);
            $this->pesquisar();
            return 'gerenciar_funcionario';
        } else if (Util::startsWithString($funcao, "paginador_")) {
            return parent::paginar($funcao);
        } else {
            return false;
        }
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
