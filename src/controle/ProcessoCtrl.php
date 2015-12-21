<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorProcesso;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Funcionario;
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
    private $validadorProcesso;
    private $post;
    private $controladores;
    
    function __construct($dao) {
        $this->descricao = "gerenciar_processo";
        $this->dao = $dao;
        $this->entidade = new Processo("");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(
                array("Nº Processo", "Funcionário", "Departamento", "Assunto"
        /* , "Movimentações" */        ));
        $assunto = new Assunto(null, true);
        $this->assuntos = $this->dao->pesquisar($assunto, PHP_INT_MAX, 0);
        $departamento = new Departamento(null, true);
        $this->departamentos = $this->dao->pesquisar($departamento, PHP_INT_MAX, 0);
        //Indexa todas os assuntos para serem buscados pela descricao
        $aux = array();
        foreach ($this->assuntos as $a) {
            $aux[$a->getDescricao()] = $a;
        }
        $this->assuntos = $aux;
        //Indexa todas os departamentos para ser buscada pela descricao
        $aux = array();
        foreach ($this->departamentos as $d) {
            $aux[$d->getDescricao()] = $d;
        }
        $this->departamentos = $aux;
        //Como o controle de processos tem apenas um funcinario que é 
        //buscado na pagina de genrenciamento de funcionarios, entao nao 
        //há necessidade de indexar essa lista, pois ela nao vai ficar em 
        //um dropdown.
        $this->funcionarios = array();
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
        //Validador utilizado para validar os campos assim como os 
        //relacionamentos do processo
        $this->validadorProcesso = new ValidadorProcesso();
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
    
    public function getValidadorProcesso() {
        return $this->validadorProcesso;
    }

    public function setValidadorProcesso($validadorProcesso) {
        $this->validadorProcesso = $validadorProcesso;
    }

    public function setFuncionarios($funcionarios) {
        if ($funcionarios != null && !empty($funcionarios)) {
            $this->entidade->setFuncionario($funcionarios[0]->clonar());
        }
    }

    public function gerarProcesso() {
        if (isset($this->post['campo_numero_processo'])) {
            $this->entidade->setNumeroProcesso($this->post['campo_numero_processo']);
        }
        if (isset($this->post['assunto']) &&
                isset($this->assuntos[$this->post['assunto']])) {
            $this->entidade->setAssunto(
                    $this->assuntos[$this->post['assunto']]->clonar());
        }
        if (isset($this->post['departamento']) &&
                isset($this->departamentos[$this->post['departamento']])) {
            $this->entidade->setDepartamento(
                    $this->departamentos[$this->post['departamento']]->clonar());
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->post = $post;
        $this->controladores = $controladores;
        
        $this->gerarProcesso();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo');
        $redirecionamento->setCtrl($this);
        //A aba/tab tabela é selecionada por padrao
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarProcesso();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarProcessos();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Funcionario("", "", "");
        } else if ($funcao == 'enviar_processos') {
            return $this->enviarProcessos();
        } else if ($funcao == 'cancelar_enviar') {
            $this->setCtrlDestino("");
            $this->setModoBusca(false);
            $this->tab = "tab_form";
        } else if ($funcao == 'remover_funcionario') {
            $this->entidade->setFuncionario(new Funcionario("", "", ""));
            $this->tab = "tab_form";
        } else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarProcesso($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirProcesso($index);
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        } else if ($funcao == 'buscar_funcionario') {
            return $this->buscarFuncionario();
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $processo) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $processo->getNumeroProcesso();
            $valores[] = $processo->getFuncionario() != null ?
                    $processo->getFuncionario()->getNome() :
                    "";
            $valores[] = $processo->getDepartamento() != null ?
                    $processo->getDepartamento()->getDescricao() :
                    "";
            $valores[] = $processo->getAssunto() != null ?
                    $processo->getAssunto()->getDescricao() :
                    "";
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarProcesso() {
        $this->validadorProcesso->validar($this->entidade);
        if (!$this->validadorProcesso->getValido()) {
            $this->mensagem = $this->validadorProcesso->getMensagem();
            $this->tab = "tab_form";
        } else {
            $this->dao->editar($this->entidade);
            $this->entidade = new Processo("");
            $this->modoEditar = false;
            $this->tab = "tab_form";
            $this->mensagem = new Mensagem(
                    "Cadastro de processos"
                    , Mensagem::MSG_TIPO_OK
                    , "Dados do Processo salvos com sucesso.");
        }
    }

    private function pesquisarProcessos() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    private function editarProcesso($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    private function excluirProcesso($index) {
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
    }

    private function enviarProcessos($post, $controladores) {
        $redirecionamento = new Redirecionamento();

        $selecionados = array();
        foreach ($this->post as $valor) {
            if (Util::startsWithString($valor, "radio_")) {
                $index = str_replace("radio_", "", $valor);
                $selecionados[] = clone $this->entidades[$index - 1];
            }
        }
        $ctrl = $this->controladores[$this->ctrlDestino];
        $ctrl->setProcessos($selecionados);
        $this->modoBusca = false;
        $redirecionamento->setDestino($this->ctrlDestino);
        $redirecionamento->setCtrl($this->controladores[$this->ctrlDestino]);
        return $redirecionamento;
    }

    private function buscarFuncionario($controladores) {
        $funcCtrl = $this->controladores['gerenciar_funcionario'];
        $funcCtrl->setModoBusca(true);
        $funcCtrl->setCtrlDestino('gerenciar_processo');
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_funcionario');
        $redirecionamento->setCtrl($funcCtrl);
        $this->tab = "tab_form";
        return $redirecionamento;
    }

    public function resetar() {
        $this->mensagem = null;
        $this->validadorProcesso = new ValidadorProcesso();
    }

}
