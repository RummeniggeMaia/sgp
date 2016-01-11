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
    private $post;
    private $controladores;

    public function __construct() {
        $this->descricao = "gerenciar_movimentacao";
        $this->entidade = new Movimentacao("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Descrição"));
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
            $this->entidade->setDescricao(strtoupper($this->post['campo_descricao']));
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->post = $post;
        $this->controladores = $controladores;

        $this->gerarMovimentacao();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_movimentacao');
        $redirecionamento->setCtrl($this);

        $this->tab = "tab_form";

        if ($funcao == "salvar") {
            $this->salvarMovimentacao();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarMovimentacao();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Movimentacao("", "");
        } else if (Util::startsWithString($funcao, "editar_")) {
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
        $this->validadorMovimentacao->validar($this->entidade);
        if (!$this->validadorMovimentacao->getValido()) {
            $this->mensagem = $this->validadorMovimentacao->getMensagem();
            
        } else {
            try {
                $this->entidade->setConstante(true);
                $log = new Log();
                if ($this->modoEditar) {
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->dao->editar($this->entidade);
                    $this->movimentacaoEditado();
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $this->movimentacaoInserido();
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
                $this->dao->editar($log);
                $this->entidade = new Movimentacao("", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de movimentação"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados de Movimentação salvo com sucesso.");
            } catch(UniqueConstraintViolationException $e){                
                $this->validadorMovimentacao->setValido(false);
                $this->validadorMovimentacao->setCamposInvalidos(array("campo_descricao"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Já existe uma movimentação com essa descrição.\n");
                                
            }catch (Exception $e) {
                $this->mensagem = new Mensagem(
                        "Cadastro de movimentação"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro ao salvar a movimentação.");
            }
        }
    }

    private function pesquisarMovimentacao() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    private function editarMovimentacao($index) {
        if ($index > 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->copiaEntidade = $this->entidade->clonar();
            $this->modoEditar = true;
            
        }
    }

    private function excluirMovimentacao($index) {

        if ($index != 0) {
            $this->copiaEntidade = $this->entidades[$index - 1];
            $this->dao->excluir($this->copiaEntidade);
            $this->movimentacaoRemovido();
            $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
            $p = $this->modeloTabela->getPaginador();
            if ($p->getOffset() == $p->getContagem()) {
                $p->anterior();
            }
            $p->setContagem($p->getContagem() - 1);
            $this->pesquisar();
            $this->mensagem = new Mensagem(
                    "Cadastro de movimentações"
                    , Mensagem::MSG_TIPO_OK
                    , "Movimentação removida com sucesso.");
        }
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorMovimentacao = new ValidadorMovimentacao();
        $this->controladores = null;
        $this->post = null;
    }

    private function gerarLog($tipo) {
        $log = new Log();
        $log->setTipo($tipo);
        $autenticacaoCtrl = $this->controladores["gerenciar_autenticacao"];
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

    private function movimentacaoInserido() {
        $pmCtrl = $this->controladores[Controlador::CTRL_PROCESSO_MOVIMENTACAO];
        $movs = $pmCtrl->getMovimentacoes();
        $movs[] = $this->copiaEntidade->clonar();
        $pmCtrl->setMovimentacoes($movs);
    }

    private function movimentacaoEditado() {
        $pmCtrl = $this->controladores[Controlador::CTRL_PROCESSO_MOVIMENTACAO];
        $movs = $pmCtrl->getMovimentacoes();
        foreach ($movs as $i => $m) {
            if ($m->getId() == $this->copiaEntidade->getId()) {
                $movs[$i] = $this->copiaEntidade->clonar();
                break;
            }
        }
        $pmCtrl->setMovimentacoes($movs);
    }

    private function movimentacaoRemovido() {
        $pmCtrl = $this->controladores[Controlador::CTRL_PROCESSO_MOVIMENTACAO];
        $movs = $pmCtrl->getMovimentacoes();
        foreach ($movs as $i => $m) {
            if ($m->getId() == $this->copiaEntidade->getId()) {
                unset($movs[$i]);
                break;
            }
        }
        $pmCtrl->setMovimentacoes($movs);
        $pms = $pmCtrl->getEntidade()->getProcessoMovimentacoes();
        foreach ($pms as $i => $pm) {
            if ($pm->getMovimentacao()->getId() ==
                    $this->copiaEntidade->getId()) {
                unset($pms[$i]);
            }
        }
        $pmCtrl->getEntidade()->setProcessoMovimentacoes($pms);
    }

}
