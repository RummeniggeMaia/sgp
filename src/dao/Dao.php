<?php

namespace dao;

/**
 *
 * @author Rummenigge
 */
class Dao {

    private $entityManager;

    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
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
        $this->entityManager->update($entidade);
    }

    public function excluir($entidade) {
        $this->entityManager->remove($entidade);
    }

    public function pesquisarTodos($entidade, $limit, $offset) {
        return $this->entityManager->getRepository($entidade->getClassName())
                        ->findAll();
    }

    public function pesquisar($entidade, $limit, $offset) {
        
    }

}
