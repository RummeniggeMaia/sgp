<?php

namespace controle;

use controle\Controlador;
use controle\tabela\ModeloDeTabela;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Processo;
use util\Util;

/**
 * Description of ProcessoCtrl
 *
 * @author Rummenigge
 */
class ProcessoCtrl extends Controlador {

    private $assuntos;
    private $departamentos;
    private $funcionarios;

    function __construct($dao) {
        $this->dao = $dao;
        $this->entidade = new Processo(0);
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(
                array("Nº Processo", "Funcionário", "Departamento", "Assunto",
                    "Movimentações"));
        $assunto = new Assunto(null, true);
        $this->assuntos = $this->dao->pesquisar($assunto, PHP_INT_MAX, 0);
        $departamento = new Departamento(null, true);
        $this->departamentos = $this->dao->pesquisar($departamento, PHP_INT_MAX, 0);
        $this->funcionarios = array();
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
    }

    public function getAssuntos() {
        return $this->assuntos;
    }

    public function getDepartamentos() {
        return $this->departamentos;
    }

    public function getFuncionarios() {
        return $this->funcionarios;
    }

    public function setAssuntos($assuntos) {
        $this->assuntos = $assuntos;
    }

    public function setDepartamentos($departamentos) {
        $this->departamentos = $departamentos;
    }

    public function setFuncionarios($funcionarios) {
        $this->funcionarios = $funcionarios;
    }

    public function gerarProcesso($post) {
        if (isset($post['campo_numero_processo'])) {
            $this->entidade->setNumeroProcesso($post['campo_numero_processo']);
        }
        if (isset($post['assunto'])) {
            foreach ($this->assuntos as $assunto) {
                if ($assunto->getId() == $post['assunto']) {
                    $this->entidade->setAssunto(clone $assunto);
                    break;
                }
            }
        }
        if (isset($post['departamento'])) {
            foreach ($this->departamentos as $departamento) {
                if ($departamento->getId() == $post['departamento']) {
                    $this->entidade->setDepartamento(clone $departamento);
                }
            }
        }
        if (isset($post['campo_funcionario'])) {
            foreach ($this->funcionarios as $funcionario) {
                if ($funcionario->getId() == $post['campo_funcionario']) {
                    $this->entidade->setFuncionario(clone $funcionario);
                }
            }
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarProcesso($post);
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo');
        $redirecionamento->setCtrl($this);

        if ($funcao == "salvar") {
            $this->dao->editar($this->entidade);
//            if ($this->modoEditar) {
//                $this->dao->editar($this->entidade);
//            } else {
//                $this->dao->criar($this->entidade);
//            }
            $this->entidade = new Processo("");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de processos"
                    , "msg_tipo_ok"
                    , "Dados do Processo salvos com sucesso.");
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->setContagem(
                    $this->dao->contar($this->entidade));
            $this->modeloTabela->getPaginador()->setPesquisa(
                    clone $this->entidade);
            $this->pesquisar();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Funcionario("", "", "");
        } else if ($funcao == 'enviar_processos') {
            foreach ($post as $chave => $valor) {
                if (Util::startsWithString($chave, "check_")) {
                    $index = str_replace("check_", "", $chave);
                    $this->entidades[$index - 1]->setSelecionado(true);
                }
            }
            $selecionados = array();
            foreach ($this->entidades as $f) {
                if ($f->getSelecionado() == true) {
                    $selecionados[] = clone $f;
                }
            }
            $ctrl = $controladores[$this->ctrlDestino];
            $ctrl->setFuncionarios($selecionados);
            $this->modoBusca = false;
            $redirecionamento->setDestino($this->getCtrlDestino());
            $redirecionamento->setCtrl($controladores[$this->getCtrlDestino()]);
            return $redirecionamento;
        } else if ($funcao == 'cancelar_enviar') {
            $this->setCtrlDestino("");
            $this->setModoBusca(false);
        } else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            if ($index != 0) {
                $this->entidade = $this->entidades[$index - 1];
                $this->modoEditar = true;
            }
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            if ($index != 0) {
                $aux = $this->entidades[$index - 1];
                $this->dao->excluir($aux);
                $p = $this->modeloTabela->getPaginador();
                if ($p->getOffset() == $p->getContagem()) {
                    $p->anterior();
                }
                $p->setContagem($p->getContagem() - 1);
                $this->pesquisar();
            }
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        } else if ($funcao == 'buscar_funcionario') {
            $funcCtrl = $controladores['gerenciar_funcionario'];
            $funcCtrl->setModoBusca(true);
            $funcCtrl->setCtrlDestino('gerenciar_processo');
            $redirecionamento = new Redirecionamento();
            $redirecionamento->setDestino('gerenciar_funcionario');
            $redirecionamento->setCtrl($funcCtrl);
            return $redirecionamento;
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $processo) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $processo->getNumeroProcesso();
            $valores[] = $processo->getFuncionario();
            $valores[] = $processo->getDepartamento();
            $valores[] = $processo->getAssunto();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

}
