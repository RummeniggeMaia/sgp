<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorMovimentacao;
use DateTime;
use DateTimeZone;
use Exception;
use modelo\Log;
use modelo\Movimentacao;
use util\Util;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Description of MovimentacaoCtrl
 *
 * @author Rummenigge
 */
class MovimentacaoCtrl extends Controlador {

    private $validadorMovimentacao;
//    private $post;
//    private $controladores;

    public function __construct() {
        $this->descricao = Controlador::CTRL_MOVIMENTACAO;
        $this->entidade = new Movimentacao("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->getPaginador()->setPesquisa(new Movimentacao("", false));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorMovimentacao = new ValidadorMovimentacao();
    }

    public function getValidadorMovimentacao() {
        return $this->validadorMovimentacao;
    }

    public function setValidadorMovimentacao($validadorMovimentacao) {
        $this->validadorMovimentacao = $validadorMovimentacao;
    }

    /**
     * Factory method para gerar movimentacões baseado a partir do POST
     */
    public function gerarMovimentacao() {
        if (isset($this->post['campo_descricao'])) {
            $this->entidade->setDescricao(trim(strtoupper($this->post['campo_descricao'])));
        }
    }

    public function executarFuncao($funcao) {
//        $this->post = $post;
//        $this->controladores = &$controladores;

        $this->gerarMovimentacao();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_MOVIMENTACAO);
        $redirecionamento->setCtrl($this);

        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarMovimentacao();
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->
                    setPesquisa($this->entidade->clonar());
            $this->pesquisarMovimentacao();
        } else if ($funcao == "cancelar_edicao") {
            $this->tab = "tab_form";
            $this->modoEditar = false;
            $this->entidade = new Movimentacao("", false);
        } else if (Util::startsWithString($funcao, "editar_")) {
            $this->tab = "tab_form";
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarMovimentacao($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirMovimentacao($index);
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $movimentacao) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $movimentacao->getDescricao();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarMovimentacao() {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $this->validadorMovimentacao->validar($this->entidade);
        if (!$this->validadorMovimentacao->getValido()) {
            $this->mensagem = $this->validadorMovimentacao->getMensagem();
            $this->tab = "tab_form";
        } else {
            try {
                $this->entidade->setConstante(true);
                $log = new Log();
                if ($this->modoEditar) {
                    $this->copiaEntidade = $this->dao->pesquisarPorId($this->entidade);
                    if ($this->copiaEntidade == null) {
                        throw new Exception("Entidade inexistente, não é possível editá-la.");
                    }
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->dao->editar($this->entidade);
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
                $this->dao->editar($log);
                $this->entidade = new Movimentacao("", "");
                $this->copiaEntidade = new Movimentacao("", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de movimentação"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados da Movimentação salvos com sucesso.");
            } catch (UniqueConstraintViolationException $e) {
                $this->validadorMovimentacao->setValido(false);
                $this->validadorMovimentacao->setCamposInvalidos(array("campo_descricao"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Já existe uma movimentação com essa descrição.\n");
            } catch (Exception $e) {
                $this->mensagem = new Mensagem(
                        "Cadastro de movimentação"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro ao salvar a movimentação: " . $e->getMessage());
            }
        }
    }

    private function pesquisarMovimentacao() {
//        $this->modeloTabela->getPaginador()->setContagem(
//                $this->dao->contar($this->modeloTabela->
//                                getPaginador()->getPesquisa()));
        $this->pesquisar();
    }

    public function editarMovimentacao($index) {
        if ($index > 0 && $index <= count($this->entidades)) {
            $aux = $this->dao->pesquisarPorId($this->entidades[$index - 1]);
            if ($aux == null) {
                $this->entidade = new Movimentacao("", false);
            } else {
                $this->entidade = $aux;
                $this->copiaEntidade = $this->entidade->clonar();
                $this->modoEditar = true;
            }
        }
    }

    private function excluirMovimentacao($index) {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        if ($index != 0) {
            $this->copiaEntidade = $this->dao->pesquisarPorId(
                    $this->entidades[$index - 1]);
            if ($this->copiaEntidade != null) {
                $this->dao->excluir($this->copiaEntidade);
                $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
                $this->mensagem = new Mensagem(
                        "Remover movimentação"
                        , Mensagem::MSG_TIPO_OK
                        , "Movimentação removida com sucesso.");
            } else {
                $this->mensagem = new Mensagem(
                        "Remover movimentação"
                        , Mensagem::MSG_TIPO_AVISO
                        , "Movimentação já foi removida por outro usuário.");
            }
        }
    }

    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Movimentacao("", false);
            } else {
                $this->entidade = $aux;
            }
        }
        $this->pesquisarMovimentacao();
    }

    public function resetar() {
        parent::resetar();
        $this->copiaEntidade = new Movimentacao("", false);
        $this->validadorMovimentacao = new ValidadorMovimentacao();
    }

    private function gerarLog($tipo) {
        $log = new Log();
        $log->setTipo($tipo);
        $autenticacaoCtrl = $this->controladores[Controlador::CTRL_AUTENTICACAO];
        $log->setUsuario($autenticacaoCtrl->getEntidade());
        $log->setDataHora(new DateTime("now", new DateTimeZone('America/Sao_Paulo')));
        $entidade = array();
        $campos = array();
        $entidade["classe"] = $this->copiaEntidade->getClassName();
        $entidade["id"] = $this->copiaEntidade->getId();
        if ($log->getTipo() == Log::TIPO_CADASTRO) {
            $log->setDadosAlterados(json_encode($entidade));
        } else if ($log->getTipo() == Log::TIPO_EDICAO) {         
            if ($this->copiaEntidade->getDescricao() != $this->entidade->getDescricao()) {
                $campos["descricao"] = $this->copiaEntidade->getDescricao();
            }
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        } else if ($log->getTipo() == Log::TIPO_REMOCAO) {
            $campos["descricao"] = $this->copiaEntidade->getDescricao();
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        }
        return $log;
    }
}
