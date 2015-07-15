<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use modelo\Funcionario;
use controle\tabela\Paginador;

/**
 * Description of FuncionarioCtrl
 *
 * @author Rummenigge
 */
class FuncionarioCtrl extends Controlador {

    public function __construct() {
        $this->funcionario = new Funcionario("", "", "");
        $this->aux = new Funcionario("", "", "");
        $this->funcionarios = array();
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
            if ($resultado == "validacao_erro") {
                return 'gerenciar_funcionario';
            }

            $this->dao->criar($this->funcionario);
            $this->funcionario = new Funcionario("", "", "");
            $this->mensagem = new Mensagem(
                    "Cadastro de funcionários"
                    , "msg_tipo_ok"
                    , "Funcionário cadastrado com sucesso.");
            return 'gerenciar_funcionario';
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->funcionario));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->funcionario);
            $this->pesquisar();
            $this->gerarLinhas();
            return 'gerenciar_funcionario';
        } else if ($funcao == "editar") {

            $resultado = $this->validacao();
            if ($resultado == "campo_nome_erro") {
                return 'gerenciar_funcionario';
            }
            return false;
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
                    , "O campo NOME não pode está vazio");
            return 'validacao_erro';
        }
        if ($this->funcionario->getRg() == null) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo RG não pode está vazio");
            return 'validacao_erro';
        }
        if ($this->funcionario->getCpf() == null) {
            $this->mensagem = new Mensagem(
                    "Cadastro não realizado!"
                    , "msg_tipo_erro"
                    , "O campo CPF não pode está vazio");
            return 'validacao_erro';
        }else if (!$this->validarCPF($this->funcionario->getCpf())) {
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
