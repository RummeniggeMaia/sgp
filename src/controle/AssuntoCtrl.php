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

    public function executarFuncao($post, $funcao,& $controladores) {
        $this->post = $post;
        $this->controladores = &$controladores;
        $this->autenticacaoCtrl = $controladores[Controlador::CTRL_AUTENTICACAO];

        $this->gerarAssunto();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_ASSUNTO);
        $redirecionamento->setCtrl($this);

        $this->tab = "tab_form";

        if ($funcao == "salvar") {
            $this->salvarAssunto();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarAssunto();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Assunto("", "");
        } else if (Util::startsWithString($funcao, "editar_")) {
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
        } else {
            try {
                $this->entidade->setConstante(true);
                $log = new Log();
                if ($this->modoEditar) {
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $this->pesquisarAssunto();
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
                $this->dao->editar($log);
                $this->entidade = new Assunto("", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de assuntos"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados do Assunto salvo com sucesso.");
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
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                $this->entidade->clonar());
        $this->pesquisar();
    }

    public function editarAssunto($index) {
        if ($index > 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->copiaEntidade = $this->entidade->clonar();
            $this->modoEditar = true;
            
        }
    }

    public function excluirAssunto($index) {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        if ($index != 0) {
            $this->copiaEntidade = $this->entidades[$index - 1];
            $this->dao->excluir($this->copiaEntidade);
            $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
            $p = $this->modeloTabela->getPaginador();
            if ($p->getOffset() == $p->getContagem()) {
                $p->anterior();
            }
            $p->setContagem($p->getContagem() - 1);
            $this->pesquisar();
            $this->mensagem = new Mensagem(
                    "Cadastro de assuntos"
                    , Mensagem::MSG_TIPO_OK
                    , "Assunto removido com sucesso.");
        }
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorAssunto = new ValidadorAssunto();
        $this->post = null;
        $this->dao = null;
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
            $this->copiaEntidade = $this->dao->pesquisarPorId($this->entidade);
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
//
//    private function assuntoInserido() {
////        $processoCtrl = $this->controladores[Controlador::CTRL_PROCESSO];
////        $assuntos = $processoCtrl->getAssuntos();
////        $assuntos[] = $this->copiaEntidade->clonar();
////        $processoCtrl->setAssuntos($assuntos);
//    }
//
//    private function assuntoEditado() {
//        $processoCtrl = $this->controladores[Controlador::CTRL_PROCESSO];
//        $assuntos = $processoCtrl->getAssuntos();
//        foreach ($assuntos as $i => $a) {
//            if ($a->getId() == $this->copiaEntidade->getId()) {
//                $assuntos[$i] = $this->copiaEntidade->clonar();
//                break;
//            }
//        }
//        $processoCtrl->setAssuntos($assuntos);
//    }
//
//    private function assuntoRemovido() {
//        $processoCtrl = $this->controladores[Controlador::CTRL_PROCESSO];
//        $assuntos = $processoCtrl->getAssuntos();
//        foreach ($assuntos as $i => $a) {
//            if ($a->getId() == $this->copiaEntidade->getId()) {
//                unset($assuntos[$i]);
//                break;
//            }
//        }
//        $processoCtrl->setAssuntos($assuntos);
//    }

    public function iniciar() {
        
    }

}
