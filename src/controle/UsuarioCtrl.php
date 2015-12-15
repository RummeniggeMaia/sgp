<?php

namespace controle;

use controle\Controlador;
use modelo\Usuario;
use dao\Dao;
use controle\Mensagem;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorUsuario;
use util\Util;

/**
 * Description of UsuarioCtrl
 *
 * @author Jaedson
 */
class UsuarioCtrl extends Controlador {

    protected $dao;
    public $validadorUsuario;

    const OFFSET = 0;
    const LIMITE = 1;

    public function __construct() {
        $this->entidade = new Usuario("", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Login"));
        $this->modeloTabela->setModoBusca(false);
        $this->validadorUsuario = new ValidadorUsuario();
    }

    public function executarFuncao($post, $funcao, $controladores) {
        $this->gerarUsuario($post);
        
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_usuario');
        $redirecionamento->setCtrl($this);
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarUsuario();
        } else if ($funcao == "pesquisar") {
            $this->pesquisarUsuario();
        } else if ($funcao == "login") {
            autenticar();
        }
        return $redirecionamento;
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $usuario) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $funcionario->getLogin();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    private function salvarUsuario() {
        $this->validadorUsuario->validar($this->entidade);
        if (!$this->validadorUsuario->getValido()) {
            $this->mensagem = $this->validadorUsuario->getMensagem();
            $this->tab = "tab_form";
        } else {
            $this->dao->editar($this->entidade);
            $this->entidade = new Usuario("", "");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Cadastro de usuários"
                    , Mensagem::MSG_TIPO_OK
                    , "Dados do usuário salvo com sucesso.");
        }
    }

    private function pesquisarUsuario() {
        $this->modeloTabela->setPaginador(new Paginador());
        $this->modeloTabela->getPaginador()->setContagem(
                $this->dao->contar($this->entidade));
        $this->modeloTabela->getPaginador()->setPesquisa(
                clone $this->entidade);
        $this->pesquisar();
    }

    private function autenticar() {
        $resultado = $this->dao->pesquisar($this->usuario, self::LIMITE, self::OFFSET);
        if ($resultado != NULL) {
            $ctrl->setEntidade($resultado);
        } else {
            // ERRO
        }
    }

    private function gerarUsuario($post) {
        if (isset($post['campo_login'])) {
            $this->entidade->setLogin($post['campo_login']);
        }
        if (isset($post['campo_senha'])) {
            $this->entidade->setSenha($post['campo_senha']);
        }
    }

    public function resetar() {
        $this->mensagem = null;
    }

}
