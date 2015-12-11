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
    const OFFSET = 0;
    const LIMITE = 1;
    
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
        $resultado = $this->dao->pesquisar($this->usuario, self::LIMITE,self::OFFSET);
        if($resultado != NULL){
            $ctrl->setEntidade($resultado);
        }else{
            // ERRO
        }
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
