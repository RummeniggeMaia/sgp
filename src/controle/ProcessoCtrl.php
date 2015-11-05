<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use DateTime;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Funcionario;
use modelo\Movimentacao;
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
    private $movimentacoes;

    function __construct($dao) {
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
        $movimentacao = new Movimentacao(null, "", true);
        $this->movimentacoes = $this->dao->pesquisar($movimentacao, PHP_INT_MAX, 0);
        //Apos as listas serem iniciadas elas vao ser indexadas.
        $this->indexarListas();
        //Como o controle de processos tem apenas um funcinario que é 
        //buscado na pagina de genrenciamento de funcionarios, entao nao 
        //há necessidade de indexar essa lista, pos ela nao vai ficar em 
        //um dropdown.
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
        if ($funcionarios != null && !empty($funcionarios)) {
            $this->entidade->setFuncionario(clone $funcionarios[0]);
        }
    }

    public function getMovimentacoes() {
        return $this->movimentacoes;
    }

    public function setMovimentacoes($movimentacoes) {
        $this->movimentacoes = $movimentacoes;
    }

    public function gerarProcesso($post) {
        if (isset($post['campo_numero_processo'])) {
            $this->entidade->setNumeroProcesso($post['campo_numero_processo']);
        }
        if (isset($post['assunto']) &&
                is_numeric($post['assunto']) &&
                $post['assunto'] > 0) {

            $this->entidade->setAssunto(
                    clone $this->assuntos[$post['assunto'] - 1]);
        }
        if (isset($post['departamento']) &&
                is_numeric($post['departamento']) &&
                $post['departamento'] > 0) {
            $this->entidade->setDepartamento(
                    clone $this->departamentos[$post['departamento'] - 1]);
        }
        foreach ($post as $k => $v) {
            if (Util::startsWithString($k, "movimentacao_")) {
                $index = intval(str_replace("movimentacao_", "", $k));
                if ($v > 0) {
                    $mov = clone $this->movimentacoes[$v - 1];
                    $mov->setDataProcesso(new DateTime('now'));
                    $this->entidade->setMovimentacaoAt($index - 1, $mov);
                }
            }
        }
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarProcesso($post);
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo');
        $redirecionamento->setCtrl($this);
        //A aba/tab tabela é selecionada por padrao
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->dao->editar($this->entidade);
            $this->entidade = new Processo("");
            $this->modoEditar = false;
            $this->tab = "tab_form";
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
            $this->tab = "tab_form";
        } else if ($funcao == 'remover_funcionario') {
            $this->entidade->setFuncionario(new Funcionario("", "", ""));
            $this->tab = "tab_form";
        } else if ($funcao == 'adicionar_movimentacao') {
            $mov = new Movimentacao(null, "", true);
            $this->entidade->addMovimentacao($mov);
            $this->tab = "tab_form";
        } else if (Util::startsWithString($funcao, "editar_")) {
            $index = intval(str_replace("editar_", "", $funcao));
            if ($index != 0) {
                $this->entidade = $this->entidades[$index - 1];
                $this->modoEditar = true;
                $this->tab = "tab_form";
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

    /**
     * Funcao utilizada para indexar todas as listas deste controlador. 
     * No contrutor as listas: assuntos, funcionarios e movimencacoes sao 
     * pesquisadas no sistema porem cada entidade nao reconhece o seu indice
     *  no vetor, essa funcao é utilizada para indexar todos os elementos de 
     * cada lista. Esse indice sera utilizado para saber qual elemento 
     * o usuario selecionou em um dropdown.
     */
    private function indexarListas() {
        foreach ($this->assuntos as $k => $v) {
            $v->setIndice($k + 1);
        }
        foreach ($this->departamentos as $k => $v) {
            $v->setIndice($k + 1);
        }
        foreach ($this->movimentacoes as $k => $v) {
            $v->setIndice($k + 1);
        }
    }

}
