<?php

namespace controle;

/**
 *
 * @author Rummenigge
 */
interface Controlador {
    
    public function executarFuncao($post, $funcao);
    
    public function getDao();
}
