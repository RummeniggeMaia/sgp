<?php

namespace controle;

use controle\Controlador;
use modelo\Usuario;
use util\Util;
use dao\Dao;

/**
 * Description of UsuarioCtrl
 *
 * @author Jaedson
 */
class UsuarioCtrl extends Controlador {
    
    protected $dao;
    
    public function __construct() {
        
    } 

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarUsuario($post);
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_usuario');
        $redirecionamento->setCtrl($this);
        $this->mensagem = null;

        if ($funcao == "login") {
            autenticar();
        }
    }

    public function gerarLinhas() {
        
    }

    private function autenticar() {
        $this->dao->pesquisar($this->usuario, "","");
    }

    private function gerarUsuario($post) {
        if (isset($post['campo_login'])) {
            $this->usuario->setLogin($post['campo_login']);
        }
        if (isset($post['campo_senha'])) {
            $this->usuario->setSenha($post['campo_senha']);
        }
        
    }

}
