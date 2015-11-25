<?php

namespace dao;

/**
 *
 * @author Rummenigge
 */
class Dao {

    /**
     *
     * @var EntityManager
     */
    private $entityManager;
    private $dqlBuilder;

    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
        $this->dqlBuilder = new DqlBuilder();
    }

    public function getEntityManager() {
        return $this->entityManager;
    }

    public function setEntityManager($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function criar($entidade) {
        $this->entityManager->persist($entidade);
        $this->entityManager->flush();
    }

    public function editar($entidade) {
        $this->entityManager->merge($entidade);
        $this->entityManager->flush();
    }

    public function excluir($entidade) {
        $entidade->setAtivo(false);
        $this->editar($entidade);
    }

    public function pesquisar($entidade, $limit, $offset) {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $this->dqlBuilder->gerarDql(
                $queryBuilder, $entidade, DqlBuilder::FUNCAO_BUSCAR);
        $queryBuilder->setMaxResults($limit);
        $queryBuilder->setFirstResult($offset);
        $result = $queryBuilder->getQuery()->getResult();
        return $this->desanexar($result);
//        return $result;
    }

    public function contar($entidade) {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $this->dqlBuilder->gerarDql(
                $queryBuilder, $entidade, DqlBuilder::FUNCAO_CONTAR);
        $result = $queryBuilder->getQuery()->getSingleScalarResult();
        return $result;
    }

    /**
     * Função utilizada para desanexar as entidades pesquisadas da ORM, 
     * a pesquisa na base de dados retorna uma lista de entidades com 
     * propriedades diferentes das classes do pacote modelo, entao é necessario 
     * converter a lista clonando cada entidade.
     */
    public function desanexar($lista) {
        $desanexados = array();
        foreach ($lista as $e) {
            $desanexados[] = $e->clonar();
        }
        return count($desanexados) > 0 ? $desanexados : $lista;
    }

}
