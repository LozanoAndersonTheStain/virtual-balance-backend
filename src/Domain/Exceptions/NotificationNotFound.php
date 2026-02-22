<?php

namespace VirtualBalance\Domain\Exceptions;

use Exception;

class NotificationNotFound extends Exception 
{
    public function __construct(string $id)
    {
        parent::__construct("Notificación con ID: {$id} no encontrada.");
    }
}