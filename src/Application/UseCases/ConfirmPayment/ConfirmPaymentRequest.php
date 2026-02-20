<?php

namespace VirtualBalance\Application\UseCases\ConfirmPayment;

/**
 * Request DTO para el caso de uso ConfirmPayment
 */
class ConfirmPaymentRequest
{
    public function __construct(
        public readonly string $token,
        public readonly string $sessionId
    ) {
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->token)) {
            $errors['token'] = 'El token es obligatorio';
        }

        if (empty($this->sessionId)) {
            $errors['sessionId'] = 'El session ID es obligatorio';
        }

        return $errors;
    }
}
