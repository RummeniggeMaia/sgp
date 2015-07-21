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
        return $queryBuilder->getQuery()->getResult();
    }

    public function contar($entidade) {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $this->dqlBuilder->gerarDql(
                $queryBuilder, $entidade, DqlBuilder::FUNCAO_CONTAR);
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

}
