<?php

namespace controle;

use controle\Controlador;
use controle\Redirecionamento;
use modelo\Usuario;

/**
 * Description of LoginCtrl
 *
 * @author Rummenigge
 */
class AutenticacaoCtrl extends Controlador {

    const OFFSET = 0;
    const LIMITE = 1;

    private $visaoAtual;
//    private $post;
//    private $controladores;

    public function __construct($dao) {
        $this->dao = $dao;
        $this->descricao = Controlador::CTRL_AUTENTICACAO;
        $this->entidade = new Usuario("", "", "", "");
        $this->visaoAtual = Controlador::CTRL_HOME;
    }

    public function getVisaoAtual() {
        return $this->visaoAtual;
    }

    public function setVisaoAtual($visaoAtual) {
        $this->visaoAtual = $visaoAtual;
    }

    public function executarFuncao($funcao) {
//        $this->post = $post;
//        $this->controladores = &$controladores;

        $this->gerarUsuario();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino($this->visaoAtual);
//        if (!isset($this->controladores[$this->visaoAtual])) {
//            $this->controladores[$this->visaoAtual] = ControladorFactory
//                    ::criarControlador(
//                            $this->visaoAtual
//                            , $this->dao->getEntityManager());
//        }
        $ctrl = $this->controladores[$this->visaoAtual];
        $ctrl->setDao($this->dao);
        $redirecionamento->setCtrl($ctrl);

        if ($funcao == "autenticar") {
            if ($this->autenticar()) {
                $redirecionamento->getCtrl()->setMensagem(
                        new Mensagem(
                        "Autenticação"
                        , Mensagem::MSG_TIPO_OK
                        , "Usuário logado com sucesso."
                ));
            } else {
                $redirecionamento->getCtrl()->setMensagem(
                        new Mensagem(
                        "Autenticação"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Credenciais inválidas"
                ));
            }
        } else if ($funcao == "sair") {
            return $this->sair();
        }
        return $redirecionamento;
    }

    private function autenticar() {
        $this->criptografarSenha();
        $this->entidade->setAutenticar(true);
        $resultado = $this->dao->pesquisar(
                $this->entidade, self::LIMITE, self::OFFSET);
        if ($resultado != NULL && count($resultado) > 0) {
            $this->entidade = $resultado[0];
            return true;
        } else {
            return false;
        }
    }

    public function sair() {
        $this->entidade = new Usuario("", "", "", "");
        unset($this->controladores[Controlador::CTRL_ASSUNTO]);
        unset($this->controladores[Controlador::CTRL_AUTENTICACAO]);
        unset($this->controladores[Controlador::CTRL_DEPARTAMENTO]);
        unset($this->controladores[Controlador::CTRL_MOVIMENTACAO]);
        unset($this->controladores[Controlador::CTRL_PROCESSO_MOVIMENTACAO]);
        unset($this->controladores[Controlador::CTRL_USUARIO]);
//        unset($this->controladores[Controlador::CTRL_AUTENTICACAO]);
        $this->controladores[Controlador::CTRL_PROCESSO] = ControladorFactory
                ::criarControlador(
                        Controlador::CTRL_PROCESSO
                        , $this->dao->getEntityManager());
        $this->controladores[Controlador::CTRL_FUNCIONARIO] = ControladorFactory
                ::criarControlador(
                        Controlador::CTRL_FUNCIONARIO
                        , $this->dao->getEntityManager());
        $this->controladores[Controlador::CTRL_HOME] = ControladorFactory
                ::criarControlador(
                        Controlador::CTRL_HOME
                        , $this->dao->getEntityManager());
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_HOME);
        $redirecionamento->setCtrl($this->controladores[Controlador::CTRL_HOME]);
        $this->dao = null;
        return $redirecionamento;
    }

    private function criptografarSenha() {
        $this->entidade->setSenha(hash("sha256", $this->entidade->getSenha()));
    }

    private function gerarUsuario() {
        if (isset($this->post['campo_login'])) {
            $this->entidade->setLogin(trim($this->post['campo_login']));
        }
        if (isset($this->post['campo_password'])) {
            $this->entidade->setSenha($this->post['campo_password']);
        }
    }

    public function gerarLinhas() {
        
    }

    public function resetar() {
        parent::resetar();
    }

    public function contemAutorizacao($a) {
        foreach ($this->entidade->getAutorizacoes() as $atual) {
            if ($atual->getDescricao() == $a) {
                return true;
            }
        }
        return false;
    }

    public function iniciar() {
        
    }

//put your code here
}
