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
    private $post;
    private $controladores;

    public function __construct() {
        $this->descricao = "gerenciar_autenticacao";
        $this->entidade = new Usuario("", "", "", "");
        $this->visaoAtual = "gerenciar_home";
    }

    public function getVisaoAtual() {
        return $this->visaoAtual;
    }

    public function setVisaoAtual($visaoAtual) {
        $this->visaoAtual = $visaoAtual;
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->post = $post;
        $this->controladores = $controladores;

        $this->gerarUsuario();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino($this->visaoAtual);
        $redirecionamento->setCtrl($controladores[$this->visaoAtual]);

        if ($funcao == "autenticar") {
            if ($this->autenticar()) {
                $redirecionamento->getCtrl()->setMensagem(
                        new Mensagem(
                        "Autenticação"
                        , Mensagem::MSG_TIPO_OK
                        , "Usuário logado com sucesso."
                ));
            }
        } else if ($funcao == "sair") {
            $this->sair();
        }
        return $redirecionamento;
    }

    private function autenticar() {
        //$this->criptografarSenha();
        $resultado = $this->dao->pesquisar(
                $this->entidade, self::LIMITE, self::OFFSET);
        if ($resultado != NULL && count($resultado) > 0) {
            $this->entidade = $resultado[0];
            return true;
        } else {
            return false;
        }
    }

    private function sair() {
        $this->entidade = new Usuario("", "", "", "");
    }

    private function criptografarSenha() {
        $this->entidade->setSenha(hash("sha256", $this->entidade->getSenha()));
    }

    private function gerarUsuario() {
        if (isset($this->post['campo_login'])) {
            $this->entidade->setLogin($this->post['campo_login']);
        }
        if (isset($this->post['campo_senha'])) {
            $this->entidade->setSenha($this->post['campo_senha']);
        }
    }

    public function gerarLinhas() {
        
    }

    public function resetar() {
        $this->post = null;
        $this->controladores = null;
    }

    public function contemAutorizacao($a) {
        foreach ($this->entidade->getAutorizacoes() as $atual) {
            if ($atual->getDescricao() == $a) {
                return true;
            }
        }
        return false;
    }
//put your code here
}
