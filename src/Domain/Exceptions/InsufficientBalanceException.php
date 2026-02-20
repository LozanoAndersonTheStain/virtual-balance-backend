<?php

namespace VirtualBalance\Domain\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public function __construct(float $required, float $available)
    {
        parent::__construct(
            "Saldo insuficiente. Requerido: {$required}, Disponible: {$available}"
        );
    }
}