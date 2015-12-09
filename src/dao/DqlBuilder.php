<?php

namespace dao;

use dao\Dql;
use Doctrine\ORM\QueryBuilder;
use modelo\Entidade;
use modelo\Funcionario;
use modelo\Usuario;

/**
 * Description of DqlBuilder
 *
 * @author Rummenigge
 */
class DqlBuilder {

    const FUNCAO_BUSCAR = 1;
    const FUNCAO_CONTAR = 2;

    public function gerarDql(QueryBuilder $queryBuilder, Entidade $entidade, $funcao) {

        if ($funcao == self::FUNCAO_BUSCAR) {
            $queryBuilder->select("x");
        } else if ($funcao == self::FUNCAO_CONTAR) {
            $queryBuilder->select("count(x)");
        }
        $queryBuilder->from($entidade->getClassName(), "x");
        if ($entidade->getClassName() == "modelo\Funcionario") {
            $this->gerarClausulaWhereFuncionario(
                    $entidade, $queryBuilder);
        }
        if ($entidade->getClassName() == "modelo\Usuario") {
            $this->gerarClausulaWhereUsuario(
                    $entidade, $queryBuilder);
        }
    }

    private function gerarClausulaWhereFuncionario(Funcionario $funcionario, QueryBuilder $qb) {

        if ($funcionario->getNome() != null &&
                preg_match("/.+/i", $funcionario->getNome())) {
            $qb->andWhere("x.nome like '%" . $funcionario->getNome() . "%'");
        }
        if ($funcionario->getCpf() != null &&
                preg_match("/.+/i", $funcionario->getCpf())) {
            $qb->andWhere("x.cpf = '" . $funcionario->getCpf() . "'");
        }
        if ($funcionario->getRg() != null &&
                preg_match("/.+/i", $funcionario->getRg())) {
            $qb->andWhere("x.rg = '" . $funcionario->getRg() . "'");
        }
    }

    private function gerarClausulaWhereUsuario(Usuario $usuario, QueryBuilder $qb) {
        if ($usuario->getLogin() != null &&
                preg_match("/.+/i", $usuario->getLogin())) {
            $qb->andWhere("x.login = '" . $usuario->getLogin() . "'");
        }
        if ($usuario->getSenha() != null &&
                preg_match("/.+/i", $usuario->getSenha())) {
            $qb->andWhere("x.senha = '" . $usuario->getSenha() . "'");
        }
        
    }

}
