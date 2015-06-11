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
        return $this->entityManager->findAll($entidade->getClassName());
    }
    
    public function pesquisar($entidade, $limit, $offset) {
        
    }
}
