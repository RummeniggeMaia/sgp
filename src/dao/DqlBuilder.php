<?php

namespace dao;

use dao\Dql;
use Doctrine\ORM\QueryBuilder;
use modelo\Entidade;
use modelo\Funcionario;
use modelo\Assunto;
use modelo\Departamento;

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
        if ($entidade->getClassName() == "modelo\Assunto") {
            $this->gerarClausulaWhereAssunto(
                    $entidade, $queryBuilder);
        }
        if ($entidade->getClassName() == "modelo\Departamento") {
            $this->gerarClausulaWhereDepartamento(
                    $entidade, $queryBuilder);
        }
    }

    private function gerarClausulaWhereFuncionario(Funcionario $funcionario, QueryBuilder $qb) {
        $qb->where("x.ativo = true");
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

    private function gerarClausulaWhereAssunto(Assunto $assunto, QueryBuilder $qb) {
        $qb->where("x.ativo = true");
        if ($assunto->getDescricao() != null &&
                preg_match("/.+/i", $assunto->getDescricao())) {
            $qb->andWhere("x.descricao like '%" . $assunto->getDescricao() . "%'");
        }
    }

    private function gerarClausulaWhereDepartamento(Departamento $departamento, QueryBuilder $qb) {
        $qb->where("x.ativo = true");
        if ($departamento->getDescricao() != null &&
                preg_match("/.+/i", $departamento->getDescricao())) {
            $qb->andWhere("x.descricao like '%" . $departamento->getDescricao() . "%'");
        }
    }

}
