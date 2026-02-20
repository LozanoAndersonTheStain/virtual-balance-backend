<?php

namespace VirtualBalance\Domain\Exceptions;

use Exception;

class TransactionNotFoundException extends Exception
{
    public function __construct(string $identifier)
    {
        parent::__construct("Transacción no encontrada: {$identifier}");
    }
}