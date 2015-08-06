<?php

namespace controle;

use util\Util;

/**
 *
 * @author Rummenigge
 */
abstract class Validador {
    protected $entidade;
    
    public abstract function validar($entidade);
    
}