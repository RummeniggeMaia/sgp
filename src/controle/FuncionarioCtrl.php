<?php

namespace controle;

use controle\Controlador;
use controle\ProcessoCtrl;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use modelo\Funcionario;
use controle\ValidadorFuncionario;
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

        if ($funcao == "salvar") {
            $resultado = $this->validadorFuncionario->validarCadastro($this->entidade);
            if ($resultado != null) {
                $this->mensagem = new Mensagem(
                        "Cadastro de funcionários"
                        , "msg_tipo_error"
                        , $resultado);
            } else {
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
            }
            $this->entidade = new Funcionario("", "", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , "msg_tipo_ok"
                    , "Dados do Funcionário salvos com sucesso.");
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
        } else if ($funcao == 'enviar_funcionarios') {
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
            $ctrl->setFuncionarios($selecionados);
            $this->modoBusca = false;
            $redirecionamento->setDestino($this->getCtrlDestino());
            $redirecionamento->setCtrl($controladores[$this->getCtrlDestino()]);
            return $redirecionamento;
        } else if ($funcao == 'cancelar_enviar') {
            $this->setCtrlDestino("");
            $this->setModoBusca(false);
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

    /* Falta resolver o problema da mascara, pois a mascara tem q ser tirada antes da verificação, para conferir se
      o CPF e RG é válido e são numéricos realmente. */

    private function validacao() {
        if ($this->entidade->getNome() == null || is_numeric($this->entidade->getNome())) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo NOME não pode está vazio");
            return 'validacao_erro';
        }

        if ($this->entidade->getRg() == null || is_numeric($this->entidade->getRg())) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo RG não pode está vazio");
            return 'validacao_erro';
        }

        if ($this->entidade->getCpf() == null || is_numeric($this->entidade->getCpf())) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo CPF é Inválido");
            return 'validacao_erro';
        }
    }

    private function validarCPF($cpf) { { // Verifiva se o número digitado contém todos os digitos
            $cpf = str_pad(ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);

            // Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
            if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
                return false;
            } else {   // Calcula os números para verificar se o CPF é verdadeiro
                for ($t = 9; $t < 11; $t++) {
                    for ($d = 0, $c = 0; $c < $t; $c++) {
                        $d += $cpf{$c} * (($t + 1) - $c);
                    }

                    $d = ((10 * $d) % 11) % 10;


                    if ($cpf{$c} != $d) {
                        return false;
                    }
                }

                return true;
            }
        }
    }

}
