<?php

namespace dao;

use Doctrine\ORM\QueryBuilder;
use modelo\Assunto;
use modelo\Departamento;
use modelo\Entidade;
use modelo\Funcionario;
use modelo\Movimentacao;
use modelo\Processo;
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
        if ($entidade->getClassName() == "modelo\Assunto") {
            $this->gerarClausulaWhereAssunto(
                    $entidade, $queryBuilder);
        }
        if ($entidade->getClassName() == "modelo\Departamento") {
            $this->gerarClausulaWhereDepartamento(
                    $entidade, $queryBuilder);
        }
        if ($entidade->getClassName() == "modelo\Movimentacao") {
            $this->gerarClausulaWhereMovimentacao(
                    $entidade, $queryBuilder);
        }
        if ($entidade->getClassName() == "modelo\Processo") {
            $this->gerarClausulaWhereProcesso(
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
        if ($usuario->getNome() != null &&
                preg_match("/.+/i", $usuario->getNome())) {
            $qb->andWhere("x.nome like '%" . $usuario->getNome() . "%'");
        }
        if ($usuario->getEmail() != null &&
                preg_match("/.+/i", $usuario->getEmail())) {
            $qb->andWhere("x.email = '" . $usuario->getEmail() . "'");
        }
        if ($usuario->getLogin() != null &&
                preg_match("/.+/i", $usuario->getLogin())) {
            $qb->andWhere("x.login = '" . $usuario->getLogin() . "'");
        }
        if ($usuario->getSenha() != null &&
                preg_match("/.+/i", $usuario->getSenha())) {
            $qb->andWhere("x.senha = '" . $usuario->getSenha() . "'");
        }
    }

    private function gerarClausulaWhereAssunto(Assunto $assunto, QueryBuilder $qb) {
        if ($assunto->getDescricao() != null &&
                preg_match("/.+/i", $assunto->getDescricao())) {
            $qb->andWhere("x.descricao like '%" . $assunto->getDescricao() . "%'");
        }
    }

    private function gerarClausulaWhereDepartamento(Departamento $departamento, QueryBuilder $qb) {
        if ($departamento->getDescricao() != null &&
                preg_match("/.+/i", $departamento->getDescricao())) {
            $qb->andWhere("x.descricao like '%" . $departamento->getDescricao() . "%'");
        }
    }

    private function gerarClausulaWhereMovimentacao(Movimentacao $movimentacao, QueryBuilder $qb) {
        if ($movimentacao->getDescricao() != null &&
                preg_match("/.+/i", $movimentacao->getDescricao())) {
            $qb->andWhere("x.descricao like '%" . $movimentacao->getDescricao() . "%'");
        }
    }
    
    private function gerarClausulaWhereProcesso(Processo $p, QueryBuilder $qb) {
        if ($p->getNumeroProcesso() != null &&
                preg_match("/.+/i", $p->getNumeroProcesso())) {
            $qb->andWhere("x.numeroProcesso = '" . $p->getNumeroProcesso() . "'");
            //$qb->andWhere("x.numeroProcesso like '%" . $p->getNumeroProcesso() . "%'");
        }
        if ($p->getAssunto()->getId() != null) {
            $qb->andWhere("x.assunto = " . $p->getAssunto()->getId());
        }
        if ($p->getDepartamento()->getId() != null) {
            $qb->andWhere("x.departamento = " . $p->getDepartamento()->getId());
        }
        if ($p->getFuncionario()->getId() != null) {
            $qb->andWhere("x.funcionario = " . $p->getFuncionario()->getId());
        }
    }

}
