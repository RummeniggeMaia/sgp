<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use modelo\Assunto;
use util\Util;

/**
 * Description of AssuntoCtrl
 *
 * @author Rummenigge
 */
class AssuntoCtrl extends Controlador {

    public function __construct() {
        $this->entidade = new Assunto("");
        $this->aux = new Assunto("");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela;
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->setModoBusca(false);
    }

    /**
     * Factory method para gerar assuntos baseado a partir do POST
     */
    public function gerarAssunto($post) {
        if (isset($post['campo_descricao'])) {
            $this->entidade->setDescricao($post['campo_descricao']);
        }
    }

    public function executarFuncao($post, $funcao) {
        $this->gerarAssunto($post);
        if ($funcao == "cadastrar") {
            $this->dao->criar($this->entidade);
            $this->assunto = new Assunto("");
            $this->mensagem = new Mensagem(
                    "Cadastro de Assuntos"
                    , "msg_tipo_ok"
                    , "Assunto cadastrado com sucesso.");
            return 'gerenciar_assunto';
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->entidade));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->entidade);
            $this->pesquisar();
            $this->gerarLinhas();
            return 'gerenciar_assunto';
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        } else {
            return false;
        }
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $assunto) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $assunto->getDescricao();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

}
