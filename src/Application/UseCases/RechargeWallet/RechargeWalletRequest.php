<?php

namespace VirtualBalance\Application\UseCases\RechargeWallet;

/**
 * Request DTO para el caso de uso RechargeWallet
 */
class RechargeWalletRequest
{
    public function __construct(
        public readonly string $document,
        public readonly float $amount
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

        if ($this->amount > 1000000) {
            $errors['amount'] = 'El monto máximo por recarga es 1.000.000';
        }

        return $errors;
    }
}
