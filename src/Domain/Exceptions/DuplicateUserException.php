<?php

namespace VirtualBalance\Domain\Exceptions;

use Exception;

class DuplicateUserException extends Exception
{
    public function __construct(string $field, string $value)
    {
        parent::__construct("Ya existe un usuario con {$field}: {$value}");
    }
}