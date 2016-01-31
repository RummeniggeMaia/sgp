<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorAssunto;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use modelo\Assunto;
use modelo\Autorizacao;
use modelo\Log;
use util\Util;
use Exception;

/**
 * Description of AssuntoCtrl
 *
 * @author Rummenigge
 */
class AssuntoCtrl extends Controlador {

    private $validadorAssunto;
    private $post;
    private $controladores;
    private $autenticacaoCtrl;

    public function __construct() {
        $this->descricao = Controlador::CTRL_ASSUNTO;
        $this->entidade = new Assunto("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela;
        $this->modeloTabela->setCabecalhos(array("Descrição"));
        $this->modeloTabela->getPaginador()->setPesquisa(new Assunto("", false));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorAssunto = new ValidadorAssunto();
    }

    public function getValidadorAssunto() {
        return $this->validadorAssunto;
    }

    public function setValidadorAssunto($validadorAssunto) {
        $this->validadorAssunto = $validadorAssunto;
    }

    /**
     * Factory method para gerar assuntos baseado a partir do POST
     */
    public function gerarAssunto() {
        if (isset($this->post['campo_descricao'])) {
            $this->entidade->setDescricao(
                    strtoupper(trim($this->post['campo_descricao'])));
        }
    }

    public function executarFuncao($post, $funcao, & $controladores) {
        $this->post = $post;
        $this->controladores = &$controladores;
        $this->autenticacaoCtrl = $controladores[Controlador::CTRL_AUTENTICACAO];

        $this->gerarAssunto();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_ASSUNTO);
        $redirecionamento->setCtrl($this);

        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarAssunto();
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->
                    setPesquisa($this->entidade->clonar());
            $this->pesquisarAssunto();
        } else if ($funcao == "cancelar_edicao") {
            $this->tab = "tab_form";
            $this->modoEditar = false;
            $this->entidade = new Assunto("", "");
        } else if (Util::startsWithString($funcao, "editar_")) {
            $this->tab = "tab_form";
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarAssunto($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirAssunto($index);
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        }
        return $redirecionamento;
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

    private function salvarAssunto() {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $this->validadorAssunto->validar($this->entidade);
        if (!$this->validadorAssunto->getValido()) {
            $this->mensagem = $this->validadorAssunto->getMensagem();
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
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
//                $this->modeloTabela->getPaginador()->
//                        setPesquisa($this->copiaEntidade->clonar());
                $this->dao->editar($log);
                $this->entidade = new Assunto("", "");
                $this->copiaEntidade = new Assunto("", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de assuntos"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados do Assunto salvos com sucesso.");
            } catch (UniqueConstraintViolationException $ex) {
                $this->validadorAssunto->setValido(false);
                $this->validadorAssunto->setCamposInvalidos(array("campo_descricao"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Já existe um assunto com essa descrição.\n");
            } catch (Exception $ex) {
                $this->mensagem = new Mensagem(
                        "Cadastro de assuntos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro ao salvar o assunto");
            }
        }
    }

    public function pesquisarAssunto() {
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->modeloTabela->
                                getPaginador()->getPesquisa()));
        $this->pesquisar();
    }

    public function editarAssunto($index) {
        if ($index > 0 && $index <= count($this->entidades)) {
            $aux = $this->dao->pesquisarPorId($this->entidades[$index - 1]);
            if ($aux == null) {
                $this->entidade = new Assunto("", false);
            } else {
                $this->entidade = $aux;
                $this->copiaEntidade = $this->entidade->clonar();
                $this->modoEditar = true;
            }
        }
    }

    public function excluirAssunto($index) {
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
                        "Remover assunto"
                        , Mensagem::MSG_TIPO_OK
                        , "Assunto removido com sucesso.");
            } else {
                $this->mensagem = new Mensagem(
                        "Remover assunto"
                        , Mensagem::MSG_TIPO_AVISO
                        , "Assunto já foi removido por outro usuário.");
            }
        }
    }

    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Assunto("", false);
            } else {
                $this->entidade = $aux;
            }
        }
        $this->pesquisarAssunto();
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorAssunto = new ValidadorAssunto();
        $this->post = null;
        $this->dao = null;
        $this->copiaEntidade = new Assunto("", false);
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
