<?php

namespace controle;

use modelo\Processo;
use modelo\Movimentacao;
use modelo\ProcessoMovimentacao;
use DateTime;

/**
 * Description of ProcessoMovimentacaoCtrl
 *
 * @author Rummenigge
 */
class ProcessoMovimentacaoCtrl extends Controlador {

    private $movimentacoes;

    /**
     * $dao usado para buscar a lista de movimentacoes do sistema
     */
    function __construct($dao) {
        $this->entidade = new Processo("");
        $movimentacao = new Movimentacao(null, "", true);
        $this->movimentacoes = $this->dao->pesquisar($movimentacao, PHP_INT_MAX, 0);
        $this->mensagem = null;
    }

    public function getMovimentacoes() {
        return $this->movimentacoes;
    }

    public function setMovimentacoes($movimentacoes) {
        $this->movimentacoes = $movimentacoes;
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo_movimentacao');
        $redirecionamento->setCtrl($this);
        //A aba/tab tabela é selecionada por padrao

        if ($funcao == "salvar") {
            $this->dao->editar($this->entidade);
            $this->entidade = new Processo("");
            $this->modoEditar = false;
            $this->tab = "tab_form";
            $this->mensagem = new Mensagem(
                    "Movimentação Processual"
                    , "msg_tipo_ok"
                    , "Movimentações cadastradas com sucesso.");
        } else if ($funcao == 'adicionar_movimentacao') {
            $pm = new ProcessoMovimentacao();
            $pm->setDataMovimentacao(new DateTime("now"));
            $pm->setProcesso($this->entidade);
            $pm->setMovimentacao(new Movimentacao("", false));
            $pms = $this->entidade->getProcessoMovimentacoes();
            $pms->add($pm);
            $this->entidade->setProcessoMovimentacoes($pms);
        } else if ($funcao == 'buscar_processo') {
            $proCtrl = $controladores['gerenciar_processo'];
            $proCtrl->setModoBusca(true);
            $proCtrl->setCtrlDestino('gerenciar_processo_movimentacao');
            $redirecionamento = new Redirecionamento();
            $redirecionamento->setDestino('gerenciar_processo');
            $redirecionamento->setCtrl($proCtrl);
            return $redirecionamento;
        } else if ($funcao == "remover_processo") {
            $this->entidade = new Processo("");
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        
    }

    public function setProcessos($list) {
        if ($list != null && count($list) > 0) {
            $this->entidade = $list[0];
        }
    }

}
