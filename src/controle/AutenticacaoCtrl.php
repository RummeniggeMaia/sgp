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

    public function __construct() {
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
        $this->gerarUsuario($post);
        
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino($this->visaoAtual);
        $redirecionamento->setCtrl($controladores[$this->visaoAtual]);
        
        if ($funcao == "autenticar") {
            $this->autenticar();
        } else if ($funcao == "sair") {
            $this->sair();
        }
        return $redirecionamento;
    }

    private function autenticar() {
        $this->criptografarSenha();
        $resultado = $this->dao->pesquisar(
                $this->entidade, self::LIMITE, self::OFFSET);
        if ($resultado != NULL && count($resultado) > 0) {
            $this->entidade = $resultado[0];
        } else {
            // ERRO
        }
    }

    private function sair() {
        $this->entidade = new Usuario("", "", "", "");
    }

    private function criptografarSenha() {
        $this->entidade->setSenha(hash("sha256", $this->entidade->getSenha()));
    }

    private function gerarUsuario($post) {
        if (isset($post['campo_login'])) {
            $this->entidade->setLogin($post['campo_login']);
        }
        if (isset($post['campo_senha'])) {
            $this->entidade->setSenha($post['campo_senha']);
        }
    }
    
    public function gerarLinhas() {
        
    }

    public function resetar() {
        
    }

//put your code here
}
