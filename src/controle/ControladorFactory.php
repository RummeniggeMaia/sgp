<?php

namespace controle;

use dao\Dao;
/**
 * Description of FactoryControlador
 *
 * @author Rummenigge
 */
class ControladorFactory {

    //FactoryMethod para criar controladores. Isso permite que apenas os 
    //controladores que o usuário está usando sejam instanciados.
    public static function criarControlador($ctrl, $entityManager) {
        $dao = new Dao($entityManager);
        switch ($ctrl) {
            case Controlador::CTRL_ASSUNTO :
                return new AssuntoCtrl();
            case Controlador::CTRL_AUTENTICACAO :
                return new AutenticacaoCtrl($dao);
            case Controlador::CTRL_DEPARTAMENTO :
                return new DepartamentoCtrl();
            case Controlador::CTRL_FUNCIONARIO :
                return new FuncionarioCtrl();
            case Controlador::CTRL_HOME :
                return new HomeCtrl();
            case Controlador::CTRL_MOVIMENTACAO :
                return new MovimentacaoCtrl();
            case Controlador::CTRL_PROCESSO :
                return new ProcessoCtrl($dao);
            case Controlador::CTRL_PROCESSO_MOVIMENTACAO :
                return new ProcessoMovimentacaoCtrl($dao);
            case Controlador::CTRL_USUARIO :
                return new UsuarioCtrl($dao);
        }
    }

}
