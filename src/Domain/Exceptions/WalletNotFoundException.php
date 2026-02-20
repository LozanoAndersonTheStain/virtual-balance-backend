<?php

namespace VirtualBalance\Domain\Exceptions;

use Exception;

class WalletNotFoundException extends Exception
{
    public function __construct(int $walletId)
    {
        parent::__construct("Billetera no encontrada con ID: {$walletId}");
    }
}