<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use modelo\Funcionario;

/**
 * Description of FuncionarioCtrl
 *
 * @author Rummenigge
 */
class FuncionarioCtrl implements Controlador {

    private $funcionario;
    private $aux;
    private $funcionarios;
    private $dao;
    private $mensagem;
    private $modeloTabela;

    public function __construct() {
        $this->funcionario = new Funcionario("", "", "");
        $this->aux = new Funcionario("", "", "");
        $this->funcionarios = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela;
        $this->modeloTabela->setCabecalhos(array("Nome", "RG", "CPF"));
        $this->modeloTabela->setModoBusca(false);
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
    }

    public function getDao() {
        return $this->dao;
    }

    public function setDao($dao) {
        $this->dao = $dao;
    }

    public function getFuncionario() {
        return $this->funcionario;
    }

    public function getAux() {
        return $this->aux;
    }

    public function setFuncionario($funcionario) {
        $this->funcionario = $funcionario;
    }

    public function setAux($aux) {
        $this->aux = $aux;
    }

    public function getFuncionarios() {
        return $this->funcionarios;
    }

    public function getModeloTabela() {
        return $this->modeloTabela;
    }

    /**
     * Factory method para gerar funcionarios baseado a partir do POST
     */
    public function gerarFuncionario($post) {
        if (isset($post['campo_nome'])) {
            $this->funcionario->setNome($post['campo_nome']);
        }
        if (isset($post['campo_cpf'])) {
            $this->funcionario->setCpf($post['campo_cpf']);
        }
        if (isset($post['campo_rg'])) {
            $this->funcionario->setRg($post['campo_rg']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarFuncionario($post);
        if ($funcao == "cadastrar") {
            $resultado = $this->validarCampos();
            if ($resultado == "validacao_erro")
                return 'gerenciar_funcionario';
        }
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->funcionario);
            $this->funcionario = new Funcionario("", "", "");
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , "msg_tipo_ok"
                    , "Funcionário cadastrado com sucesso.");
            return 'gerenciar_funcionario';
        } else if ($funcao == "pesquisar") {
            $this->funcionarios = $this->dao->pesquisarTodos($this->funcionario, 0, 0);
            $this->gerarLinhas();
            return 'gerenciar_funcionario';
        } else {
            return false;
        }
    }

    private function gerarLinhas() {
        $linhas = array();
        foreach ($this->funcionarios as $funcionario) {
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

    private function validarCampos() {

        if ($this->funcionario->getNome() == null) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo nome não pode ser vazio");
            return 'validacao_erro';
        }
        if ($this->funcionario->getRg() == null) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo RG não pode ser vazio");
            return 'validacao_erro';
        }
        if ($this->funcionario->getCpf() == null) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo CPF não pode ser vazio");
            return 'validacao_erro';
        }
        if (!($this->validarCPF($this->funcionario->getCpf()))) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "CPF inválido!");
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
