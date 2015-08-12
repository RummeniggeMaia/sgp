<?php

namespace controle;

use controle\Controlador;
use controle\FuncionarioCtrl;
use controle\tabela\ModeloDeTabela;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Processo;
use dao\Dao;

/**
 * Description of ProcessoCtrl
 *
 * @author Rummenigge
 */
class ProcessoCtrl extends Controlador {

    private $assuntos;
    private $departamentos;
    private $funcionario;
    
    function __construct($dao) {
        $this->dao = $dao;
        $this->entidade = new Processo(0);
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(
                array("Nº Processo", "Funcionario", "Departamento", "Assunto",
                    "Movimentações"));
        $assunto = new Assunto(null, true);
        $this->assuntos = $this->dao->pesquisar($assunto, PHP_INT_MAX, 0);
        $departamento = new Departamento(null, true);
        $this->departamentos = $this->dao->pesquisar($departamento, PHP_INT_MAX, 0);
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

    public function getFuncionario() {
        return $this->funcionario;
    }

    public function setAssuntos($assuntos) {
        $this->assuntos = $assuntos;
    }

    public function setDepartamentos($departamentos) {
        $this->departamentos = $departamentos;
    }

    public function setFuncionario($funcionario) {
        $this->funcionario = $funcionario;
    }

    public function executarFuncao($post, $funcao, $controladores) {
        if ($funcao == 'buscar_funcionario') {
            $funcCtrl = $controladores['gerenciar_funcionario'];
            $funcCtrl->setModoBusca(true);
            $funcCtrl->setCtrlDestino('gerenciar_processo');
            return 'gerenciar_funcionario';
        }
    }
    
    public function gerarLinhas() {
        
    }

}