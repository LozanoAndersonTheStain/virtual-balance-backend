<?php

namespace VirtualBalance\Application\UseCases\MakePayment;

/**
 * Request DTO para el caso de uso MakePayment
 */
class MakePaymentRequest
{
    public function __construct(
        public readonly string $document,
        public readonly float $amount,
        public readonly ?string $description = null
    ) {
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->document)) {
            $errors['document'] = 'El documento es obligatorio';
        }

        if ($this->amount <= 0) {
            $errors['amount'] = 'El monto debe ser mayor a 0';
        }

        if ($this->amount > 500000) {
            $errors['amount'] = 'El monto máximo por pago es 500.000';
        }

        return $errors;
    }
}
