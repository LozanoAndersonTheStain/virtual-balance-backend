<?php

namespace VirtualBalance\Application\UseCases\CheckBalance;

/**
 * Request DTO para el caso de uso CheckBalance
 */
class CheckBalanceRequest
{
    public function __construct(
        public readonly string $document
    ) {
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->document)) {
            $errors['document'] = 'El documento es obligatorio';
        }

        return $errors;
    }
}
