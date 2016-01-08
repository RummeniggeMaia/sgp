<?php

namespace controle\validadores;

use controle\Mensagem;
use controle\validadores\Validador;
use modelo\Processo;

/**
 * Description of ValidadorProcesso
 *
 * @author Rummenigge
 */
class ValidadorProcesso extends Validador {

    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->mensagem = new Mensagem(
                'Dados inválidos'
                , Mensagem::MSG_TIPO_ERRO
                , 'Dados do Processo estão inválidos.');
        $submensagens = array();

        if ($entidade->getNumeroProcesso() == null) {
            $submensagens[] = "Campo Número Processo obrigatório!\n";
            $this->camposInvalidos[] = "campo_numero_processo";
        } else {
            try {
                // nada a fazer
            } catch (Exception $ex) {
                $submensagens[] = "Número de processo já cadastrado!\n" + $ex;
            }
        }

        if ($entidade->getAssunto() == null) {
            $submensagens[] = "Assunto inexistente!\n";
            $this->camposInvalidos[] = "drop_assunto";
        } else if ($entidade->getAssunto()->getId() == null) {
            $submensagens[] = "Selecione um Assunto!\n";
            $this->camposInvalidos[] = "drop_assunto";
        }

        if ($entidade->getDepartamento() == null) {
            $submensagens[] = "Departamento inexistente!\n";
            $this->camposInvalidos[] = "drop_departamento";
        } else if ($entidade->getDepartamento()->getId() == null) {
            $submensagens[] = "Selecione um Departamento!\n";
            $this->camposInvalidos[] = "drop_departamento";
        }

        if ($entidade->getFuncionario() == null) {
            $submensagens[] = "Funcionário inexistente!\n";
            $this->camposInvalidos[] = "campo_funcionario";
        } else if ($entidade->getFuncionario()->getId() == null) {
            $submensagens[] = "Selecione um Funcionario!\n";
            $this->camposInvalidos[] = "campo_funcionario";
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

//put your code here
}
