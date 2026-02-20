<?php

namespace VirtualBalance\Domain\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(string $identifier)
    {
        parent::__construct("Usuario no encontrado: {$identifier}");
    }
}