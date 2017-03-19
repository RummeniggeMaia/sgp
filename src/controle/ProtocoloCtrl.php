<?php

namespace controle;

use controle\Controlador;
use controle\Mensagem;
use modelo\Protocolo;

/**
 * Description of ProtocoloCtrl
 *
 * @author jrpmaia
 */
class ProtocoloCtrl extends Controlador {

    function __construct() {
        $this->descricao = Controlador::CTRL_PROTOCOLO;
        $this->entidade = new Protocolo();
        $this->copiaEntidade = new Protocolo();
        $this->entidades = array();
        $this->mensagem = null;
    }

    private function gerarProtocolo() {
        if (isset($this->post['campo_numero'])) {
            $this->entidade->setNumero(
                    trim($this->post['campo_numero']));
        }
    }

    //put your code here
    public function executarFuncao($funcao) {
        $this->entidade = new Protocolo();
        $this->gerarProtocolo();

        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_PROTOCOLO);
        $redirecionamento->setCtrl($this);

        if ($funcao == 'autenticar') {
            $prots = $this->dao->pesquisar($this->entidade, 1, 0);
            if (!empty($prots)) {
                $this->entidade = $prots[0];
                $this->mensagem = new Mensagem(
                        "Autenticar Processo"
                        , Mensagem::MSG_TIPO_OK
                        , "Processo consta no sistema.");
            } else {
                $this->mensagem = new Mensagem(
                        "Autenticar Processo"
                        , Mensagem::MSG_TIPO_ERRO
                        , "Processo não consta no sistema.");
            }
        }

        return $redirecionamento;
    }

    public function gerarLinhas() {
        
    }

    public function iniciar() {
        if ($this->entidade->getId() != null) {
            $aux = $this->dao->pesquisarPorId($this->entidade);
            if ($aux == null) {
                $this->entidade = new Protocolo();
            } else {
                $this->entidade = $aux;
            }
        }
    }

}
