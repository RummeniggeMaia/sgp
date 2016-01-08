<?php

namespace controle;

use controle\validadores\ValidadorProcessoMovimentacao;
use DateTime;
use DateTimeZone;
use modelo\Log;
use modelo\Movimentacao;
use modelo\Processo;
use modelo\ProcessoMovimentacao;
use util\Util;


/**
 * Description of ProcessoMovimentacaoCtrl
 *
 * @author Rummenigge
 */
class ProcessoMovimentacaoCtrl extends Controlador {

    private $validadorProcessoMovimentacao;
    private $movimentacoes;
    private $controladores;
    private $post;

    /**
     * $dao usado para buscar a lista de movimentacoes do sistema
     */
    function __construct($dao) {
        $this->descricao = "gerenciar_processo_movimentacao";
        $this->dao = $dao;
        $this->entidade = new Processo("");
        $movimentacao = new Movimentacao(null, "", true);
        //Inicia lista de movimentacoes fazendo a busca de todas as 
        //movimentacoes constantes do sistema.

        $this->validadorProcessoMovimentacao = new ValidadorProcessoMovimentacao();
        $this->movimentacoes = $this->dao->pesquisar(
                $movimentacao, PHP_INT_MAX, 0);
        //Indexa todas as movimentacoes para ser buscada pela descricao
        $aux = array();
        $aux[] = new Movimentacao("");
        foreach ($this->movimentacoes as $mov) {
            $aux[$mov->getDescricao()] = $mov;
        }
        $this->movimentacoes = $aux;
        $this->mensagem = null;

        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
    }

    public function getMovimentacoes() {
        return $this->movimentacoes;
    }

    public function setMovimentacoes($movimentacoes) {
        $this->movimentacoes = $movimentacoes;
    }

    public function getValidadorProcessoMovimentacao() {
        return $this->validadorProcessoMovimentacao;
    }

    public function setValidadorProcessoMovimentacao($validadorProcessoMovimentacao) {
        $this->validadorProcessoMovimentacao = $validadorProcessoMovimentacao;
    }

    /*
     * Toda requisicao, que é feita no form processo movimentacao, passa por 
     * esse metodo para saber o que de fato o usuário modificou no formulario.
     * Se ele modificou um campo de texto entao o novo valor vai ser atribuido 
     * a entidade na qual aquele campo de texto pertence.
     */

    public function gerarProcessoMovimentacao($post) {
        /*
         * Caso o usuario tenha modificado o valor de alguma movimentacao no 
         * form, entao essa modificacao deve ser atribuida ao modelo para que 
         * haja sincronia entre a pagina e o controle.
         */
        foreach ($post as $k => $v) {
            //Verifica se existe algum alteracao nas movimentacoes. Caso haja 
            //algum valor de $post que comece com movimentacao_ entao a 
            //movimentacao daquele processoMovimentacao foi atualizado.
            if (Util::startsWithString($k, "movimentacao_")) {
                //index representa a posicao do processoMovimentacao alterado
                $index = intval(str_replace("movimentacao_", "", $k));
                //$v é valor do dropdown e movimentacao_ é nome do mesmo
                $mov = isset($this->movimentacoes[$v]) ?
                        $this->movimentacoes[$v]->clonar() :
                        new Movimentacao("", "");
                $pms = $this->entidade->getProcessoMovimentacoes();
                //O {{ loop.index }} sempre retorna valores acima de 0, entao a 
                //opcao selecionada na posicao 0 do drop tem o index 1,
                // por isso é decrementado.
                $pm = $pms->get($index - 1);
                //Atualiza a movimentacao e o processoMovimentacao.
                $pm->setMovimentacao($mov);
                $pm->setProcesso($this->entidade);
            }
        }
    }

    /**
     * Essa funcao é chamada sempre pelo front_controller, cada entidade possui 
     * o seu controler.
     * 
     * @param type $post A requisicao do tipo post.
     * @param type $funcao A funcionalidae do controller que sera executada.
     * @param type $controladores Representa os demais controladores do sistema,
     * caso haja necessi de comicao entre eles.
     * @return Redirecionamento O controle a quem esse vai se 
     * direcionar apos executar as funcoes.
     */
    public function executarFuncao($post, $funcao, $controladores) {
        $this->controladores = $controladores;
        $this->post = $post;
        //Verifica o que mudou na pagina de gerenciarMovimentacao
        $this->gerarProcessoMovimentacao($post);

        //O redirecionamento padrao sempre retorna para a pagina atual, nesse 
        //caso a pagina desse controlador.
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo_movimentacao');
        $redirecionamento->setCtrl($this);

        if ($funcao == "salvar") {
            //Salva as alteracoes na basde
            $this->salvarProcessoMovimentacao();
        } else if ($funcao == 'adicionar_movimentacao') {
            //Adiciona um processoMovimentacao no Processo ($this->entidade).
            $this->adicionarMovimentacao();
        } else if ($funcao == 'buscar_processo') {
            //Gera um redirecionamento para a pagina de processos, apos o 
            //usuario selecionar o processo la, a funcao setProcessos deste 
            //controle sera chamada la, entao o sistema redireciona para ca 
            //novamente.
            return $this->buscarProcesso();
        } else if ($funcao == "remover_processo") {
            //Caso o usuario queira remover o processo que ele acabou de 
            //pesquisar, essa funcao sera chamada.
            $this->entidade = new Processo("");
        } else if (Util::startsWithString($funcao, "remover_movimentacao_")) {
            $index = intval(str_replace("remover_movimentacao_", "", $funcao));
            $this->removerMovimentacao($index);
        }
        //Redireciona para esta pagina.
        return $redirecionamento;
    }

    public function gerarLinhas() {
        //Nao precisa gerar as linhas, pois a interface de ProcessoMovimentacao 
        //nao tem tabela para pesquisa
    }

    //Funcao executada ProcessoCtrl apos usuario selecionar o processo.
    public function setProcessos($list) {
        //É possivel retornar uma lista, mas este controle precisa apenas da 
        //primeira entidade.
        if ($list != null && count($list) > 0) {
            $this->entidade = $list[0];
        }
    }

    private function salvarProcessoMovimentacao() {
        $this->validadorProcessoMovimentacao->validar($this->entidade);
        if (!$this->validadorProcessoMovimentacao->getValido()) {
            $this->mensagem = $this->validadorProcessoMovimentacao->getMensagem();
        } else {
            //Usa a funcao merge do Dao, pos estamos trabalhando com entidade
            // desanexadas.
            $log = $this->gerarLog(Log::TIPO_EDICAO);
            $pms = $this->entidade->getProcessoMovimentacoes();
            foreach ($pms as $key => $pm) {
                $id = $pm->getId();
                if ($id == null) {
                    $salvo = $this->dao->editar($pm);
                    $pms[$key] = $salvo;
                }
            }
            $this->entidade->setProcessoMovimentacoes($pms);
            $this->dao->editar($this->entidade);
            $this->dao->editar($log);
            $this->entidade = new Processo("");
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Movimentação Processual"
                    , "msg_tipo_ok"
                    , "Movimentações cadastradas com sucesso.");
        }
    }

    private function adicionarMovimentacao() {
        $pm = new ProcessoMovimentacao();
        $pm->setDataMovimentacao(
                new DateTime("now", new DateTimeZone('America/Sao_Paulo')));
        $pm->setProcesso($this->entidade);
        $pm->setMovimentacao(new Movimentacao("", false));
        $pms = $this->entidade->getProcessoMovimentacoes();
        $pms->add($pm);
        $this->entidade->setProcessoMovimentacoes($pms);
    }

    private function buscarProcesso() {
        $processoCtrl = $this->controladores["gerenciar_processo"];
        //Configura o controle de processo e redireciona para la
        $processoCtrl->setModoBusca(true);
        $processoCtrl->setCtrlDestino('gerenciar_processo_movimentacao');
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino('gerenciar_processo');
        $redirecionamento->setCtrl($processoCtrl);
        return $redirecionamento;
    }

    public function resetar() {
        $this->mensagem = null;
        $this->controladores = null;
        $this->post = null;
    }

    private function gerarLog($tipo) {
        $log = new Log();
        $log->setTipo($tipo);
        $autenticacaoCtrl = $this->controladores["gerenciar_autenticacao"];
        $log->setUsuario($autenticacaoCtrl->getEntidade());
        $log->setDataHora(new DateTime("now", new DateTimeZone('America/Sao_Paulo')));
        $entidade = array();
        $campos = array();
        $entidade["classe"] = $this->entidade->getClassName();
        $entidade["id"] = $this->entidade->getId();
        $movs = array();
        foreach ($this->entidade->getProcessoMovimentacoes() as $pm) {
            $id = $pm->getId();
            if ($id == null) {
                $movs[] = $pm->getMovimentacao()->getId();
            }
        }
        $campos["movimentacoes"] = $movs;
        $entidade["campos"] = $campos;
        $log->setDadosAlterados(json_encode($entidade));
        return $log;
    }

    private function removerMovimentacao($index) {
        $pms = $this->entidade->getProcessoMovimentacoes();
        if ($index > 0 && $index <= count($pms)) {
            $pm = $pms[$index - 1];
            if ($pm->getId() == null) {
                unset($pms[$index - 1]);
            }
            $this->entidade->setProcessoMovimentacoes($pms);
        }
    }

}
