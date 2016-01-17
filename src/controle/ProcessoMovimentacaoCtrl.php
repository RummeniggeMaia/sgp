<?php

namespace controle;

use controle\validadores\ValidadorProcessoMovimentacao;
use dao\Dao;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;
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
    private $pmRemovidos;

    /**
     * $dao usado para buscar a lista de movimentacoes do sistema
     */
    function __construct($dao) {
        $this->descricao = Controlador::CTRL_PROCESSO_MOVIMENTACAO;
        $this->dao = $dao;
        $this->entidade = new Processo("");
        $this->validadorProcessoMovimentacao = new ValidadorProcessoMovimentacao();
        $this->mensagem = null;
        $this->pmRemovidos = new ArrayCollection();
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
    }

    public function setEntidade($entidade) {
        parent::setEntidade($entidade);
        $this->pmRemovidos = new ArrayCollection();
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
    public function executarFuncao($post, $funcao, & $controladores) {
        $this->controladores = &$controladores;
        $this->post = $post;
        //Verifica o que mudou na pagina de gerenciarMovimentacao
        $this->gerarProcessoMovimentacao($post);

        //O redirecionamento padrao sempre retorna para a pagina atual, nesse 
        //caso a pagina desse controlador.
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_PROCESSO_MOVIMENTACAO);
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
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
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
            foreach ($this->pmRemovidos as $pm) {
                try {
                    $this->dao->excluir($pm);
                } catch (ORMException $ex) {
                    $this->mensagem = new Mensagem(
                            "Movimentação Processual"
                            , "msg_tipo_erro"
                            , "Erro ao remover movimentação: "
                            . $pm->getMovimentacao()->getDescricao());
                }
            }
            $this->entidade->setProcessoMovimentacoes($pms);
            $this->dao->editar($this->entidade);
            $this->dao->editar($log);
            $this->entidade = new Processo("");
            $this->pmRemovidos = new ArrayCollection();
            $this->modoEditar = false;
            $this->mensagem = new Mensagem(
                    "Movimentação Processual"
                    , "msg_tipo_ok"
                    , "Movimentações cadastradas com sucesso.");
        }
    }

    private function adicionarMovimentacao() {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
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
        $processoCtrl = $this->controladores[Controlador::CTRL_PROCESSO];
        //Configura o controle de processo e redireciona para la
        $processoCtrl->setModoBusca(true);
        $processoCtrl->setCtrlDestino(Controlador::CTRL_PROCESSO_MOVIMENTACAO);
        $processoCtrl->setDao(new Dao($this->dao->getEntityManager()));
        $redirecionamento = new Redirecionamento();
        $redirecionamento->setDestino(Controlador::CTRL_PROCESSO);
        $redirecionamento->setCtrl($processoCtrl);
        return $redirecionamento;
    }

    public function resetar() {
        //Depois q esse contrutor for chamado no index.php, esse controlador vai 
        //ser serializado, por isso o objeto dao tem q ser nulado pois o mesmo 
        //nao pode ser serializado
        $this->dao = null;
        $this->mensagem = null;
        $this->post = null;
        $this->validadorProcessoMovimentacao = new ValidadorProcessoMovimentacao();
    }

    private function gerarLog($tipo) {
        $log = new Log();
        $log->setTipo($tipo);
        $autenticacaoCtrl = $this->controladores[Controlador::CTRL_AUTENTICACAO];
        $log->setUsuario($autenticacaoCtrl->getEntidade());
        $log->setDataHora(new DateTime("now", new DateTimeZone('America/Sao_Paulo')));
        $entidade = array();
        $campos = array();
        $entidade["classe"] = $this->entidade->getClassName();
        $entidade["id"] = $this->entidade->getId();
        $this->copiaEntidade = $this->dao->pesquisarPorId($this->entidade);
        $movs = array();
        foreach ($this->copiaEntidade->getProcessoMovimentacoes() as $pm) {
            $movs[] = $pm->getMovimentacao()->getId();
        }
        $campos["movimentacoes_id"] = $movs;
        $entidade["campos"] = $campos;
        $log->setDadosAlterados(json_encode($entidade));
        return $log;
    }

    private function removerMovimentacao($index) {
        if (!$this->verificarPermissao(
                        $this->controladores[Controlador::CTRL_AUTENTICACAO])) {
            return;
        }
        $pms = $this->entidade->getProcessoMovimentacoes();
        if ($index > 0 && $index <= count($pms)) {
            //Apos as movimentações serem removidas do processo, 
            //elas serão apagadas da base de dados.
            $pm = $pms->remove($index - 1);
            if ($pm->getId() != null) {
                $this->pmRemovidos->add($pm);
            }
            // Após remover uma movimentação, é ncessário reindexar todo o 
            // vetor, infelizmente o array do doctrine e o array do php não
            //  fazem isso
            $values = array_values($pms->toArray());
            $this->entidade->setProcessoMovimentacoes(
                    new ArrayCollection($values));
        }
    }

    public function iniciar() {
        $movimentacao = new Movimentacao(null, "", true);
        //Inicia lista de movimentacoes fazendo a busca de todas as 
        //movimentacoes constantes do sistema.
        $this->movimentacoes = $this->dao->pesquisar(
                $movimentacao, PHP_INT_MAX, 0);
        //Indexa todas as movimentacoes para ser buscada pela descricao
        $aux = array();
        foreach ($this->movimentacoes as $mov) {
            $aux[$mov->getDescricao()] = $mov;
        }
        $this->movimentacoes = $aux;
        if ($this->entidade->getId() != null) {
            $novaMovimentacao = false;
            $pms = $this->entidade->getProcessoMovimentacoes();
            foreach ($pms as $pm) {
                if ($pm->getId() == null) {
                    $novaMovimentacao = true;
                    break;
                }
            }
            $this->entidade = $this->dao->pesquisarPorId($this->entidade);
            $pms = $this->entidade->getProcessoMovimentacoes();
            foreach ($this->pmRemovidos as $pmRemovido) {
                foreach ($pms as $i => $pm) {
                    if ($pmRemovido->getId() == $pm->getId()) {
                        $pms->remove($i);
                    }
                }
            }

            $this->entidade->setProcessoMovimentacoes(
                    new ArrayCollection(array_values($pms->toArray())));
            if ($novaMovimentacao) {
                $this->adicionarMovimentacao();
            }
        }
    }

}
