<?php

namespace controle;

//use PHPMailer;


use controle\Controlador;
use controle\Mensagem;
use controle\Redirecionamento;
use controle\tabela\Linha;
use controle\tabela\ModeloDeTabela;
use controle\tabela\Paginador;
use controle\validadores\ValidadorUsuario;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use modelo\Autorizacao;
use modelo\Log;
use modelo\Usuario;
use util\Util;

/**
 * Description of UsuarioCtrl
 *
 * @author Jaedson
 */
class UsuarioCtrl extends Controlador {

    private $validadorUsuario;
    private $autorizacaoAdmin;
    
    public function __construct($dao) {
        $this->descricao = Controlador::CTRL_USUARIO;
        $this->entidade = new Usuario("", "", "", "");
        $this->entidades = array();
        $this->mensagem = null;
        $this->modeloTabela = new ModeloDeTabela();
        $this->modeloTabela->setCabecalhos(array("Nome", "Email", "Login", "Senha"));
        $this->modeloTabela->setModoBusca(false);
        $this->modeloTabela->getPaginador()->setPesquisa(new Usuario("", "", "", ""));
        $this->validadorUsuario = new ValidadorUsuario();
        
//        $auts = $dao->pesquisar(new Autorizacao("admin"), 1, 0);
//        if (count($auts) > 0) {
//            $this->autorizacaoAdmin = $auts[0];
//        }
        $a = new Autorizacao("");
        $a->setId(1);
        $this->autorizacaoAdmin = $dao->pesquisarPorId($a);
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
    }

    public function getValidadorUsuario() {
        return $this->validadorUsuario;
    }

    public function setValidadorUsuario($validadorUsuario) {
        $this->validadorUsuario = $validadorUsuario;
    }

    public function executarFuncao($funcao) {
//        $this->post = $post;
//        $this->controladores = &$controladores;

        $this->gerarUsuario();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_USUARIO);
        $redirecionamento->setCtrl($this);
        
        $this->tab = "tab_tabela";

        if ($funcao == "salvar") {
            $this->salvarUsuario();
        } else if ($funcao == "pesquisar") {
            $this->modeloTabela->setPaginador(new Paginador());
            $this->modeloTabela->getPaginador()->
                    setPesquisa($this->entidade->clonar());
            $this->pesquisarUsuario();
        }  else if ($funcao == "cancelar_edicao") {
            $this->tab = "tab_form";
            $this->modoEditar = false;
            $this->entidade = new Usuario("", "", "", "");
        } else if (Util::startsWithString($funcao, "editar_")) {
            $this->tab = "tab_form";
            $index = intval(str_replace("editar_", "", $funcao));
            $this->editarUsuario($index);
        } else if (Util::startsWithString($funcao, "excluir_")) {
            $index = intval(str_replace("excluir_", "", $funcao));
            $this->excluirUsuario($index);
        } else if (Util::startsWithString($funcao, "paginador_")) {
            parent::paginar($funcao);
        }
        return $redirecionamento;
    }

    private function salvarUsuario() {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $this->validadorUsuario->validar($this->entidade);
        if (!$this->validadorUsuario->getValido()) {
            $this->mensagem = $this->validadorUsuario->getMensagem();
            $this->tab = "tab_form";
        } else {
            try {
                $this->criptografarSenha();
                $this->atribuirAdmin();
                $log = new Log();
                if ($this->modoEditar) {
                    $this->copiaEntidade = $this->dao->pesquisarPorId($this->entidade);
                    if ($this->copiaEntidade == null) {
                        throw new Exception("Entidade inexistente, não é possível editá-la.");
                    }
                    $log = $this->gerarLog(Log::TIPO_EDICAO);
                    $this->dao->editar($this->entidade);
                } else {
                    $this->copiaEntidade = $this->dao->editar($this->entidade);
                    $log = $this->gerarLog(Log::TIPO_CADASTRO);
                }
                $this->dao->editar($log);
                $this->entidade = new Usuario("", "", "", "");
                $this->copiaEntidade = new Usuario("", "", "", "");
                $this->modoEditar = false;
                $this->mensagem = new Mensagem(
                        "Cadastro de usuários"
                        , Mensagem::MSG_TIPO_OK
                        , "Dados do Usuário salvos com sucesso.");
            } catch (UniqueConstraintViolationException $e) {
                $this->validadorUsuario->setValido(false);
                $this->validadorUsuario->setCamposInvalidos(array("campo_login"));
                $this->mensagem = new Mensagem(
                        "Dados inválidos"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Já existe um usuário com este login.\n");
            } catch (Exception $e) {
                $this->mensagem = new Mensagem(
                        "Cadastro de usuário"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Erro: " . $e->getMessage());
            }
        }
    }

    private function pesquisarUsuario() {
//        $this->modeloTabela->setPaginador(new Paginador());
//        $this->modeloTabela->getPaginador()->setContagem(
//                $this->dao->contar($this->entidade));
//        $this->modeloTabela->getPaginador()->setPesquisa(
//                $this->entidade->clonar());
        $this->pesquisar();
    }

    private function criptografarSenha() {
        $this->entidade->setSenha(hash("sha256", $this->entidade->getSenha()));
    }

    private function gerarUsuario() {
        if (isset($this->post['campo_nome'])) {
            $this->entidade->setNome(trim($this->post['campo_nome']));
        }
        if (isset($this->post['campo_email'])) {
            $this->entidade->setEmail(trim($this->post['campo_email']));
        }
        if (isset($this->post['campo_login'])) {
            $this->entidade->setLogin(trim($this->post['campo_login']));
        }
        if (isset($this->post['campo_senha'])) {
            $this->entidade->setSenha(trim($this->post['campo_senha']));
        }
    }

    public function resetar() {
        parent::resetar();
        $this->copiaEntidade = new Usuario("", "", "", "");
        $this->validadorUsuario = new ValidadorUsuario();
    }

    public function gerarLinhas() {
        $linhas = array();
        foreach ($this->entidades as $usuario) {
            $linha = new Linha();
            $valores = array();
            $valores[] = $usuario->getNome();
            $valores[] = $usuario->getEmail();
            $valores[] = $usuario->getLogin();
            $valores[] = $usuario->getSenha();
            $linha->setValores($valores);
            $linhas[] = $linha;
        }
        $this->modeloTabela->setLinhas($linhas);
    }

    public function editarUsuario($index) {
        if ($index > 0 && $index <= count($this->entidades)) {
            $aux = $this->dao->pesquisarPorId($this->entidades[$index - 1]);
            if ($aux == null) {
                $this->entidade = new Usuario("", "", "", "");
            } else {
                $this->entidade = $aux;
                $this->copiaEntidade = $this->entidade->clonar();
                $this->modoEditar = true;
            }
        }
    }

    private function excluirUsuario($index) {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        if ($index != 0) {
            $this->copiaEntidade = $this->dao->pesquisarPorId(
                    $this->entidades[$index - 1]);
            if ($this->copiaEntidade != null) {
                $this->dao->excluir($this->copiaEntidade);
                $this->dao->editar($this->gerarLog(Log::TIPO_REMOCAO));
                $this->mensagem = new Mensagem(
                        "Remover usuário"
                        , Mensagem::MSG_TIPO_OK
                        , "Usuário removido com sucesso.");
            } else {
                $this->mensagem = new Mensagem(
                        "Remover usuário"
                        , Mensagem::MSG_TIPO_AVISO
                        , "Usuário já foi removido por outro usuário.");
            }
        }
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
            if ($this->copiaEntidade->getNome() != $this->entidade->getNome()) {
                $campos["nome"] = $this->copiaEntidade->getNome();
            }
            if ($this->copiaEntidade->getEmail() != $this->entidade->getEmail()) {
                $campos["email"] = $this->copiaEntidade->getEmail();
            }
            if ($this->copiaEntidade->getLogin() != $this->entidade->getLogin()) {
                $campos["login"] = $this->copiaEntidade->getLogin();
            }
            if ($this->copiaEntidade->getSenha() != $this->entidade->getSenha()) {
                $campos["senha"] = $this->copiaEntidade->getSenha();
            }
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        } else if ($log->getTipo() == Log::TIPO_REMOCAO) {
            $campos["nome"] = $this->copiaEntidade->getNome();
            $campos["email"] = $this->copiaEntidade->getEmail();
            $campos["login"] = $this->copiaEntidade->getLogin();
            $campos["senha"] = $this->copiaEntidade->getSenha();
            $entidade["campos"] = $campos;
            $log->setDadosAlterados(json_encode($entidade));
        }
        return $log;
    }

    private function atribuirAdmin() {
        $auts = $this->entidade->getAutorizacoes();
        if (count($auts) == 0) {
            $auts[] = $this->autorizacaoAdmin->clonar();
            $this->entidade->setAutorizacoes($auts);
        }
    }

    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Usuario("", "", "", "");
            } else {
                $this->entidade = $aux;
            }
        }
        $this->pesquisarUsuario();
    }

    /* private function enviarEmail() {

      // Inclui o arquivo class.phpmailer.php localizado na pasta phpmailer
      // Inicia a classe PHPMailer
      $mail = new PHPMailer();
      // Define os dados do servidor e tipo de conexão
      // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
      $mail->IsSMTP(); // Define que a mensagem será SMTP
      $mail->Host = "smtp.dominio.net"; // Endereço do servidor SMTP
      //$mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)
      //$mail->Username = 'seumail@dominio.net'; // Usuário do servidor SMTP
      //$mail->Password = 'senha'; // Senha do servidor SMTP
      // Define o remetente
      // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
      $mail->From = "rj@st.net"; // Seu e-mail
      $mail->FromName = "RJ Soluções Tecnológicas"; // Seu nome
      // Define os destinatário(s)
      // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
      $mail->AddAddress("'" + $this->entidade->getEmail() + "'", "'" + $this->entidade . getNome() + "'");
      //$mail->AddAddress('ciclano@site.net');
      //$mail->AddCC('ciclano@site.net', 'Ciclano'); // Copia
      //$mail->AddBCC('fulano@dominio.com.br', 'Fulano da Silva'); // Cópia Oculta
      // Define os dados técnicos da Mensagem
      // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
      $mail->IsHTML(true); // Define que o e-mail será enviado como HTML
      //$mail->CharSet = 'iso-8859-1'; // Charset da mensagem (opcional)
      // Define a mensagem (Texto e Assunto)
      // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
      $mail->Subject = "Senha cadastrada"; // Assunto da mensagem
      $mail->Body = "Este é o corpo da mensagem de teste, em <b>HTML</b>!  :)";
      $mail->AltBody = "Este é o corpo da mensagem de teste, em Texto Plano! \r\n :)";
      // Define os anexos (opcional)
      // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
      //$mail->AddAttachment("c:/temp/documento.pdf", "novo_nome.pdf");  // Insere um anexo
      // Envia o e-mail
      $enviado = $mail->Send();
      // Limpa os destinatários e os anexos
      $mail->ClearAllRecipients();
      $mail->ClearAttachments();
      // Exibe uma mensagem de resultado
      if ($enviado) {
      echo "E-mail enviado com sucesso!";
      } else {
      echo "Não foi possível enviar o e-mail.";
      echo "<b>Informações do erro:</b> " . $mail->ErrorInfo;
      }
      } */
}
