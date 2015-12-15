<?php

namespace validadores;

use util\Util;

/**
 *
 * @author Rummenigge
 */
abstract class Validador {
    
    protected $entidade;
    protected $mensagem;
    
    public abstract function validar($entidade);
    
}