<?php

namespace controle\tabela;

/**
 *
 * @author Rummenigge
 */
class Paginador {

    private $contagem;
    private $offset;
    private $limit;
    private $pesquisa;
    private $limites;

    function __construct() {
        $this->limit = 5;
        $this->offset = 0;
        $this->contagem = 0;
        $this->pesquisa = null;
        $this->limites = array('5', '10', '15', '30');
    }

    public function getContagem() {
        return $this->contagem;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getPesquisa() {
        return $this->pesquisa;
    }

    public function setContagem($contagem) {
        $this->contagem = $contagem;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function setPesquisa($pesquisa) {
        $this->pesquisa = $pesquisa;
    }

    public function primeira() {
        $this->offset = 0;
    }

    public function anterior() {
        $this->offset -= $this->limit;
        if ($this->offset < 0) {
            $this->offset = 0;
        }
    }

    public function proxima() {
        if ($this->offset + $this->limit < $this->contagem) {
            $this->offset += $this->limit;
        }
    }

    public function ultima() {
        $this->offset = ($this->totalDePaginas() - 1) * $this->limit;
    }

    public function paginaAtual() {
        if ($this->contagem > 0) {
            return intval($this->offset / $this->limit + 1);
        }
        return 0;
    }

    public function totalDePaginas() {
        $quociente = intval($this->contagem / $this->limit);
        if ($this->contagem % $this->limit != 0) {
            return $quociente + 1;
        } else {
            return $quociente;
        }
    }

    public function podeVoltar() {
        return $this->paginaAtual() > 1;
    }

    public function podeSeguir() {
        return $this->paginaAtual() < $this->totalDePaginas();
    }

    public function pular($pagina) {
        if ($pagina > 0 && $pagina <= $this->totalDePaginas()) {
            $this->offset = ($pagina - 1) * $this->limit;
        }
    }

    public function resetar() {
        $this->contagem = 0;
        $this->offset = 0;
        $this->limit = 5;
        $this->pesquisa = null;
    }

    public function getLimites() {
        return $this->limites;
    }

}
