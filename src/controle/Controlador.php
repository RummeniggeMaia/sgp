<?php

namespace controle;

use modelo\Autorizacao;
use util\Util;

/**
 *
 * @author Rummenigge
 */
abstract class Controlador {

    protected $descricao;
    protected $entidade;
//    Copia da entidade utilizada apenas para armazenar o estado anterior no log
    protected $copiaEntidade;
    protected $entidades;
    protected $dao;
    protected $mensagem;
    protected $modeloTabela;
    protected $modoEditar;
    protected $modoBusca;
    //Controlador que receberá as entidades selecionadas, deve ser String 
    //apenas pra acessar o controlador de destino no vetor de controladores
    protected $ctrlDestino;
    //Variavel usada apenas acionar uma determinada tab na durante a resposta, 
    //quando o usuario clica em editar entao a tab de form tem q aparecer em vez
    // da tab que contem a tabela
    protected $tab = "tab_form";
    protected $controladores;
    protected $post;

    const CTRL_USUARIO = "gerenciar_usuario";
    const CTRL_FUNCIONARIO = "gerenciar_funcionario";
    const CTRL_ASSUNTO = "gerenciar_assunto";
    const CTRL_DEPARTAMENTO = "gerenciar_departamento";
    const CTRL_MOVIMENTACAO = "gerenciar_movimentacao";
    const CTRL_PROCESSO = "gerenciar_processo";
    const CTRL_PROCESSO_MOVIMENTACAO = "gerenciar_processo_movimentacao";
    const CTRL_HOME = "gerenciar_home";
    const CTRL_AUTENTICACAO = "gerenciar_autenticacao";
    const CTRL_PROTOCOLO = "autenticar_processo";

    public function getDescricao() {
        return $this->descricao;
    }

    public function getEntidade() {
        return $this->entidade;
    }

    public function getAssunto() {
        return $this->assunto;
    }

    public function getEntidades() {
        return $this->entidades;
    }

    public function getDao() {
        return $this->dao;
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function getModeloTabela() {
        return $this->modeloTabela;
    }

    public function setEntidade($entidade) {
        $this->entidade = $entidade;
    }

    public function setAssunto($assunto) {
        $this->assunto = $assunto;
    }

    public function setEntidades($entidades) {
        $this->entidades = $entidades;
    }

    public function setDao($dao) {
        $this->dao = $dao;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
    }

    public function setModeloTabela($modeloTabela) {
        $this->modeloTabela = $modeloTabela;
    }

    public function getModoEditar() {
        return $this->modoEditar;
    }

    public function setModoEditar($modoEditar) {
        $this->modoEditar = $modoEditar;
    }

    public function getCtrlDestino() {
        return $this->ctrlDestino;
    }

    public function setCtrlDestino($ctrlDestino) {
        $this->ctrlDestino = $ctrlDestino;
    }

    public function getModoBusca() {
        return $this->modoBusca;
    }

    public function setModoBusca($modoBusca) {
        $this->modoBusca = $modoBusca;
    }

    public function getTab() {
        return $this->tab;
    }

    public function setTab($tab) {
        $this->tab = $tab;
    }

    function getControladores() {
        return $this->controladores;
    }

    function setControladores(& $controladores) {
        $this->controladores = & $controladores;
    }

    function getPost() {
        return $this->post;
    }

    function setPost($post) {
        $this->post = $post;
    }

    public function pesquisar() {
        if ($this->modeloTabela->getPaginador()->getPesquisa() != null) {
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->modeloTabela->
                                    getPaginador()->getPesquisa()));
            $this->entidades = $this->dao->pesquisar(
                    $this->modeloTabela->getPaginador()->getPesquisa()
                    , $this->modeloTabela->getPaginador()->getLimit()
                    , $this->modeloTabela->getPaginador()->getOffset());
            $this->gerarLinhas();
        }
    }

    public function paginar($acao) {
        $paginador = $this->modeloTabela->getPaginador();
        if ($acao == "paginador_primeira") {
            $paginador->primeira();
        } else if ($acao == "paginador_anterior") {
            $paginador->anterior();
        } else if ($acao == "paginador_proxima") {
            $paginador->proxima();
        } else if ($acao == "paginador_ultima") {
            $paginador->ultima();
        } else if (Util::startsWithString($acao, "paginador_pular_")) {
            $pagina = str_replace("paginador_pular_", "", $acao);
            $paginador->pular($pagina);
        } else if (Util::startsWithString($acao, "paginador_limit_")) {
            $limit = str_replace("paginador_limit_", "", $acao);
            if ($paginador->getLimit() != $limit) {
                $paginador->setOffset(0);
                $paginador->setLimit($limit);
                $paginador->setContagem(
                        $this->dao->contar($this->entidade));
            }
        }
        $this->pesquisar();
    }

    public function verificarPermissao($autenticacaoCtrl) {
        if (!$autenticacaoCtrl->contemAutorizacao(Autorizacao::ADMIN)) {
            $this->mensagem = new Mensagem(
                    "Permissão negada"
                    , Mensagem::MSG_TIPO_ERRO
                    , "Usuário não tem permissão para executar tal função.");
            return false;
        }
        return true;
    }

    /**
     * Todas as funcoes dos controladores sao executadas a partir daqui, 
     * qualquer funcao do sistema q nao esteja dentro desta nao será executada.
     */
    //public abstract function executarFuncao($post, $funcao, & $controladores);
    public abstract function executarFuncao($funcao);

    /**
     * Cada entidade tem seus campos, entao essa funcao é utilizada para gerar 
     * a estrutura de colunas e linhas da tabela de pesquisa
     */
    public abstract function gerarLinhas();

    /**
     * Funcao invocada no front_controller para resetar o controle. 
     * Ela é chamada apos a tela ser renderizada. 
     * Serve apenas para apagar a mensagem, validadores, $_POST, array 
     * controladores, etc. Essas informacoes nao podem ser armazenadas na 
     * sessao, por isso sao apagadas no reset.
     */
    public function resetar() {
        $this->mensagem = null;     
        $this->post = null;
        $this->dao = null;
    }

    public abstract function iniciar();
}
