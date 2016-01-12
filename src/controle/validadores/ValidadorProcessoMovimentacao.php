<?php

namespace controle\validadores;

use controle\Mensagem;

/**
 * Description of ValidadorProcessoMovimentacao
 *
 * @author Rummenigge
 */
class ValidadorProcessoMovimentacao extends Validador {

    //put your code here
    public function __construct() {
        $this->mensagem = new Mensagem("", "", "");
        $this->camposInvalidos = array();
        $this->valido = false;
    }

    public function validar($entidade) {
        $this->entidade = $entidade;
        $this->mensagem = new Mensagem(
                'Dados inválidos', Mensagem::MSG_TIPO_ERRO, 'Dados da Movimentação estão inválidos.');
        $submensagens = array();

        $pms = $entidade->getProcessoMovimentacoes();
        if ($this->entidade->getId() == null) {
            $submensagens[] = "Não existe um processo a ser salvo! \n";
            $this->camposInvalidos[] = "campo_numero_processo";
        }
        foreach ($pms as $key => $pm) {
            if ($pm->getMovimentacao()->getId() == null) {
                $submensagens[] = "Movimentação " . ($key + 1) . " vazia!\n";
                $this->camposInvalidos[] = "movimentacao_" . ($key + 1);
            }
        }
        for ($i = 0; $i < count($pms); $i++) {
            for ($j = $i + 1; $j < count($pms); $j++) {
                if ($pms[$i]->getMovimentacao()->getId() ==
                        $pms[$j]->getMovimentacao()->getId()) {
                    $submensagens[] = "Movimentações " . ($i + 1) . " e " . ($j + 1) . " são iguais!\n";
                    $this->camposInvalidos[] = "movimentacao_" . ($i + 1);
                    $this->camposInvalidos[] = "movimentacao_" . ($j + 1);
                }
            }
        }

        $this->mensagem->setSubmensagens($submensagens);
        if (empty($this->camposInvalidos)) {
            $this->valido = true;
        }
    }

}
