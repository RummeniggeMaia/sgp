<?php

namespace modelo;

use modelo\Entidade;

/**
 *
 * @author Rummenigge
 * @Entity 
 * @Table(name="processo_movimentacao")
 */
class ProcessoMovimentacao extends Entidade {

    /** @Column(type="date") */
    protected $dataMovimentacao;

    /** @ManyToOne(targetEntity="Processo", inversedBy="processoMovimentacoes") */
    protected $processo;

    /** @ManyToOne(targetEntity="Movimentacao", inversedBy="processoMovimentacoes")
     *  @JoinColumn(name="movimentacao_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $movimentacao;

    public function __construct() {
        
    }

    public function getDataMovimentacao() {
        return $this->dataMovimentacao;
    }

    public function setDataMovimentacao($dataMovimentacao) {
        $this->dataMovimentacao = $dataMovimentacao;
    }

    public function getProcesso() {
        return $this->processo;
    }

    public function setProcesso($processo) {
        $this->processo = $processo;
    }

    public function getMovimentacao() {
        return $this->movimentacao;
    }

    public function setMovimentacao($movimentacao) {
        $this->movimentacao = $movimentacao;
    }

    public function getClassName() {
        $rc = new \ReflectionClass($this);
        return $rc->getName();
    }

    public function clonar() {
        $clone = new ProcessoMovimentacao();

        $clone->setId($this->id);
        $clone->setSelecionado($this->selecionado);

        $clone->setDataMovimentacao($this->dataMovimentacao);

        $clone->setMovimentacao(
                $this->movimentacao == null ?
                        new Movimentacao("", false) :
                        $this->movimentacao->clonar());
        return $clone;
    }

}
