<?php

namespace controle;

use DateTime;
use modelo\Movimentacao;
use modelo\Processo;
use modelo\ProcessoMovimentacao;
use util\Util;

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
        $this->dao = $dao;
        $this->entidade = new Processo("");
        $movimentacao = new Movimentacao(null, "", true);
        $this->movimentacoes = $this->dao->pesquisar($movimentacao, PHP_INT_MAX, 0);
        $this->mensagem = null;

        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
    }

    public function getMovimentacoes() {
        return $this->movimentacoes;
    }

    public function setMovimentacoes($movimentacoes) {
        $this->movimentacoes = $movimentacoes;
    }

    public function gerarProcessoMovimentacao($post) {
        foreach ($post as $k => $v) {
            if (Util::startsWithString($k, "movimentacao_")) {
                $index = intval(str_replace("movimentacao_", "", $k));
                if ($v > 0) {
                    $mov = $this->movimentacoes[$v - 1]->clonar();
                    $pms = $this->entidade->getProcessoMovimentacoes();
                    $pm = $pms->get($index - 1);
                    $pm->setMovimentacao($mov);
                    $pm->setProcesso($this->entidade);
                    $pm->setIndice($index);
                }
            }
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarProcessoMovimentacao($post);

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo_movimentacao');
        $redirecionamento->setCtrl($this);

        if ($funcao == "salvar") {
            $this->dao->editar($this->entidade);
            $this->entidade = new Processo("");
            $this->modoEditar = false;
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
        //Nao precisa gerar as linhas, pois a interface de ProcessoMovimentacao 
        //nao tem tabela para pesquisa
    }

    public function setProcessos($list) {
        if ($list != null && count($list) > 0) {
            $this->entidade = $list[0];

            foreach ($this->entidade->getProcessoMovimentacoes() as $pm) {
                foreach ($this->movimentacoes as $k => $m) {
                    if ($pm->getMovimentacao()->getId() == $m->getId()) {
                        $pm->setIndice($k + 1);
                        break;
                    }
                }
            }
        }
    }

}
