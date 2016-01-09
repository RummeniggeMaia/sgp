<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorProcesso;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Funcionario;
use modelo\Log;
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
        //Como o controle de processos tem apenas um funcinario que é 
        //buscado na pagina de genrenciamento de funcionarios, entao nao 
        //há necessidade de indexar essa lista, pois ela nao vai ficar em 
        //um dropdown.
        $this->funcionarios = array();
        //Validador utilizado para validar os campos assim como os 
        //relacionamentos do processo
        $this->validadorProcesso = new ValidadorProcesso();
        $this->pesquisarProcessos();
        $this->atualizarListas();
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
        //A tab tabela é selecionada por padrao
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarProcesso();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarProcessos();
        } else if ($funcao == "cancelar_edicao") {
            $this->modoEditar = false;
            $this->entidade = new Processo("", "", "");
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
            try {
                $log = new Log();
                if ($this->modoEditar) {
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $this->processoEditado();
                    $this->pesquisarProcessos();
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }

                $this->dao->editar($log);
                $ctrlPM = $this->controladores["gerenciar_processo_movimentacao"];
                if ($ctrlPM->getEntidade()->getId() == $this->copiaEntidade->getId()) {
                    $ctrlPM->setEntidade(new Processo(""));
                }
                $this->entidade = new Processo("");
                $this->modoEditar = false;
                $this->tab = "tab_form";
                $this->mensagem = new Mensagem(
                        "Cadastro de processos"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados do Processo salvos com sucesso.");
            } catch (UniqueConstraintViolationException $ex) {
                $this->validadorProcesso->setValido(false);
                $this->validadorProcesso->setCamposInvalidos(array("campo_numero_processo"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "O Número de Processo já existe cadastrado no sistema.\n");
                $this->tab = "tab_form";
            } catch (Exception $e) {
                $this->mensagem = new Mensagem(
                        "Cadastro de processos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro ao salvar o processos.\n");
            }
        }
    }

    private function pesquisarProcessos() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                $this->entidade->clonar());
        $this->pesquisar();
    }

    private function editarProcesso($index) {
        if ($index != 0) {
            $this->entidade = $this->entidades[$index - 1];
            $this->copiaEntidade = $this->entidade->clonar();
            $this->modoEditar = true;
            $this->tab = "tab_form";
        }
    }

    private function excluirProcesso($index) {
        if ($index != 0) {
            $this->copiaEntidade = $this->entidades[$index - 1];
            $this->dao->excluir($this->copiaEntidade);
            $this->processoRemovido();
            $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
            $p = $this->modeloTabela->getPaginador();
            if ($p->getOffset() == $p->getContagem()) {
                $p->anterior();
            }
            $p->setContagem($p->getContagem() - 1);
            $this->pesquisar();
            $this->mensagem = new Mensagem(
                    "Cadastro de processos"
                    , Mensagem::MSG_TIPO_OK
                    , "Processo removido com sucesso.");
        }
    }

    private function enviarProcessos() {
        $redirecionamento = new Redirecionamento();

        $selecionados = array();
        foreach ($this->post as $valor) {
            if (Util::startsWithString($valor, "radio_")) {
                $index = str_replace("radio_", "", $valor);
                $selecionados[] = $this->entidades[$index - 1]->clonar();
            }
        }
        $ctrl = $this->controladores[$this->ctrlDestino];
        $ctrl->setProcessos($selecionados);
        $this->modoBusca = false;
        $this->entidades = array();
        $this->modeloTabela->setLinhas(array());
        $redirecionamento->setDestino($this->ctrlDestino);
        $redirecionamento->setCtrl($this->controladores[$this->ctrlDestino]);
        return $redirecionamento;
    }

    private function buscarFuncionario() {
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
        $this->post = null;
        $this->controladores = null;
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
            if ($this->copiaEntidade->getNumeroProcesso() !=
                    $this->entidade->getNumeroProcesso()) {
                $campos["numeroProcesso"] = $this->copiaEntidade->getNumeroProcesso();
            }
            if ($this->copiaEntidade->getFuncionario()->getId() !=
                    $this->entidade->getFuncionario()->getId()) {
                $campos["funcionario"] = $this->copiaEntidade->getFuncionario()->getId();
            }
            if ($this->copiaEntidade->getAssunto()->getId() !=
                    $this->entidade->getAssunto()->getId()) {
                $campos["assunto"] = $this->copiaEntidade->getAssunto()->getId();
            }
            if ($this->copiaEntidade->getDepartamento()->getId() !=
                    $this->entidade->getDepartamento()->getId()) {
                $campos["departamento"] = $this->copiaEntidade->getDepartamento()->getId();
            }
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        } else if ($log->getTipo() == Log::TIPO_REMOCAO) {
            $campos["numeroProcesso"] = $this->copiaEntidade->getNumeroProcesso();
            $campos["funcionario"] = $this->copiaEntidade->getFuncionario()->getId();
            $campos["departamento"] = $this->copiaEntidade->getDepartamento()->getId();
            $campos["assunto"] = $this->copiaEntidade->getAssunto()->getId();
            $movs = array();
            foreach ($this->copiaEntidade->getProcessoMovimentacoes() as $pm) {
                $movs[] = $pm->getMovimentacao()->getId();
            }
            $campos["movimentacoes"] = $movs;
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        }
        return $log;
    }

    private function atualizarListas() {
        $assunto = new Assunto(null, true);
        $this->assuntos = $this->dao->pesquisar($assunto, PHP_INT_MAX, 0);
        $departamento = new Departamento(null, true);
        $this->departamentos = $this->dao->pesquisar($departamento, PHP_INT_MAX, 0);
        //Indexa todas os assuntos para serem buscados pela descricao
        $aux = array();
        $aux[] = new Assunto("", "");
        foreach ($this->assuntos as $a) {
            $aux[$a->getDescricao()] = $a;
        }
        $this->assuntos = $aux;
        //Indexa todas os departamentos para ser buscada pela descricao
        $aux = array();
        $aux[] = new Departamento("");
        foreach ($this->departamentos as $d) {
            $aux[$d->getDescricao()] = $d;
        }
        $this->departamentos = $aux;
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
    }

    private function processoInserido() {
        
    }

    private function processoEditado() {
        $proMovCtrl = $this->controladores[Controlador::CTRL_PROCESSO_MOVIMENTACAO];
        $pro = $proMovCtrl->getEntidade();
        if ($pro != null && $pro->getId() == $this->copiaEntidade->getId()) {
            $proMovCtrl->setEntidade($this->copiaEntidade->clonar());
        }
    }

    private function processoRemovido() {
        $proMovCtrl = $this->controladores[Controlador::CTRL_PROCESSO_MOVIMENTACAO];
        $pro = $proMovCtrl->getEntidade();
        if ($pro != null && $pro->getId() == $this->copiaEntidade->getId()) {
            $proMovCtrl->setEntidade(new Processo(""));
        }
    }

}
