<?php

namespace controle\validadores;

use controle\Mensagem;
use controle\validadores\Validador;

class ValidadorFuncionario extends Validador {

    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = new Mensagem(
                'Dados inválidos', 
                Mensagem::MSG_TIPO_ERRO, 
                'Dados do funcionário estão inválidos.');
        $submensagens = array();

        if ($this->entidade->getNome() == null ||
                $this->entidade->getNome() == "") {
            $submensagens[] = "Campo Nome obrigatório!\n";
            $this->camposInvalidos[] = "campo_nome";
        } else if (strlen($this->entidade->getNome()) < 4) {
            $submensagens[] = "Campo Nome tem que ter ao menos 4 letras!\n";
            $this->camposInvalidos[] = "campo_nome";
        } else if (!preg_match("/^([ a-zA-Z'\-áéíóúãÃÁÉÍÓÚâêîôûÂÊÎÔÛãõçÇ])+$/i", 
                $this->entidade->getNome())) {
             $submensagens[] = "Caracteres inválidos no nome!\n";
            $this->camposInvalidos[] = "campo_nome";
        }
        
        if ($this->entidade->getCpf() == null) {
            $submensagens[] = "Campo CPF obrigatório!\n";
            $this->camposInvalidos[] = "campo_cpf";
        } else if (!$this->validarCPF($this->entidade->getCpf())) {
            $submensagens[] = "Campo CPF inválido!\n";
            $this->camposInvalidos[] = "campo_cpf";
        }

        if ($this->entidade->getRg() == null) {
            $submensagens[] = "Campo RG obrigatório!\n";
            $this->camposInvalidos[] = "campo_rg";
        } else if (!preg_match("/\d{3}\.\d{3}\.\d{3}/i"
                        , $this->entidade->getRg())) {
            $submensagens[] = "Formato incorreto de RG!\n";
            $this->camposInvalidos[] = "campo_rg";
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

    private function validarCPF($cpf = null) {

        // Verifica se um número foi informado
        if (empty($cpf)) {
            return false;
        }

        // Elimina possivel mascara
        $cpf = preg_replace('/[^0-9]/i', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' ||
                $cpf == '11111111111' ||
                $cpf == '22222222222' ||
                $cpf == '33333333333' ||
                $cpf == '44444444444' ||
                $cpf == '55555555555' ||
                $cpf == '66666666666' ||
                $cpf == '77777777777' ||
                $cpf == '88888888888' ||
                $cpf == '99999999999') {
            return false;
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {

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
