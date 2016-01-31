<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorProcesso;
use dao\Dao;
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
        $this->descricao = Controlador::CTRL_PROCESSO;
        $this->dao = $dao;
        $this->entidade = new Processo("");
        $this->entidades = array();
        $this->mensagem = null;
        $this->tab = "tab_tabela";
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(
                array("Nº Processo", "Funcionário", "Departamento", "Assunto"));
        $this->modeloTabela->getPaginador()->setLimit("15");
        $this->modeloTabela->getPaginador()->setPesquisa(new Processo(""));
        $this->funcionarios = array();
        //Validador utilizado para validar os campos assim como os 
        //relacionamentos do processo
        $this->validadorProcesso = new ValidadorProcesso();
        $this->pesquisarProcessos();
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
            $this->entidade->setNumeroProcesso(trim($this->post['campo_numero_processo']));
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

    public function executarFuncao($post, $funcao, & $controladores) {
        $this->post = $post;
        $this->controladores = &$controladores;

        $this->gerarProcesso();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_PROCESSO);
        $redirecionamento->setCtrl($this);
        //A tab tabela é selecionada por padrao
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarProcesso();
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->
                    setPesquisa($this->entidade->clonar());
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
            $this->tab = "tab_form";
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
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $this->validadorProcesso->setDao($this->dao);
        $this->validadorProcesso->validar($this->entidade);
        if (!$this->validadorProcesso->getValido()) {
            $this->mensagem = $this->validadorProcesso->getMensagem();
            $this->tab = "tab_form";
        } else {
            try {
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
                $this->dao->editar($log);
                $this->entidade = new Processo("");
                $this->copiaEntidade = new Processo("");
                $this->modoEditar = false;
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
                        "Erro ao salvar o processo"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro: " . $e->getMessage());
            }
        }
    }

    private function pesquisarProcessos() {
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->modeloTabela->
                                getPaginador()->getPesquisa()));
        $this->pesquisar();
    }

    private function editarProcesso($index) {
        if ($index > 0 && $index <= count($this->entidades)) {
            $aux = $this->dao->pesquisarPorId($this->entidades[$index - 1]);
            if ($aux == null) {
                $this->entidade = new Processo("");
            } else {
                $this->entidade = $aux;
                //Quando o usuario seleciona um processo pra editar, é 
                //necessario armazenar o estado atual em outra variavel para 
                //depois colocar a mudança ono log.
                $this->copiaEntidade = $this->entidade->clonar();
                $this->modoEditar = true;
            }
        }
    }

    private function excluirProcesso($index) {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            $this->tab = "tab_form";
            return;
        }
        if ($index != 0) {
            $this->copiaEntidade = $this->dao->pesquisarPorId(
                    $this->entidades[$index - 1]);
            if ($this->copiaEntidade != null) {
                $this->dao->excluir($this->copiaEntidade);
                $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
                $this->mensagem = new Mensagem(
                        "Remover processo"
                        , Mensagem::MSG_TIPO_OK
                        , "Processo removido com sucesso.");
            } else {
                $this->mensagem = new Mensagem(
                        "Remover processo"
                        , Mensagem::MSG_TIPO_AVISO
                        , "Processo já foi removido por outro usuário.");
            }
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
        $ctrl->setDao(new Dao($this->dao->getEntityManager()));
        $this->modoBusca = false;
        $this->entidades = array();
        $this->modeloTabela->setLinhas(array());
        $redirecionamento->setDestino($this->ctrlDestino);
        $redirecionamento->setCtrl($this->controladores[$this->ctrlDestino]);
        return $redirecionamento;
    }

    private function buscarFuncionario() {
        if (!isset($this->controladores[Controlador::CTRL_FUNCIONARIO])) {
            $this->controladores[Controlador::CTRL_FUNCIONARIO] = ControladorFactory
                    ::criarControlador(
                            Controlador::CTRL_FUNCIONARIO
                            , $this->dao->getEntityManager());
        }
        $funcCtrl = $this->controladores[Controlador::CTRL_FUNCIONARIO];
        $funcCtrl->setModoBusca(true);
        $funcCtrl->setCtrlDestino(Controlador::CTRL_PROCESSO);
        $funcCtrl->setDao(new Dao($this->dao->getEntityManager()));
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_FUNCIONARIO);
        $redirecionamento->setCtrl($funcCtrl);
        $this->tab = "tab_form";
        return $redirecionamento;
    }

    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Processo("");
            } else {
                if ($this->entidade->getFuncionario()->getId() != null &&
                        $this->entidade->getFuncionario()->getId() !=
                        $aux->getFuncionario()->getId()) {
                    $aux->setFuncionario($this->entidade->getFuncionario());
                }
                $this->entidade = $aux;
            }
        }
        $assunto = new Assunto(null, true);
        $this->assuntos = $this->dao->pesquisar($assunto, PHP_INT_MAX, 0);
        $departamento = new Departamento(null, true);
        $this->departamentos = $this->dao->pesquisar($departamento, PHP_INT_MAX, 0);
        //Indexa todas os assuntos para serem buscados pela descricao
        $aux = array();
        $aux[""] = new Assunto("", "");
        foreach ($this->assuntos as $a) {
            $aux[$a->getDescricao()] = $a;
        }
        $this->assuntos = $aux;
        //Indexa todas os departamentos para ser buscada pela descricao
        $aux = array();
        $aux[""] = new Departamento("");
        foreach ($this->departamentos as $d) {
            $aux[$d->getDescricao()] = $d;
        }
        $this->departamentos = $aux;
        $aux = null;
        $this->pesquisarProcessos();
    }

    public function resetar() {
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
        $this->mensagem = null;
        $this->validadorProcesso = new ValidadorProcesso();
        $this->post = null;
        $this->copiaEntidade = new Processo("");
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

}
