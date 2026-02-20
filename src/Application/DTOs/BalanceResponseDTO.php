<?php

namespace VirtualBalance\Application\DTOs;

/**
 * DTO para respuesta de consulta de saldo
 */
class BalanceResponseDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $userName,
        public readonly string $document,
        public readonly int $walletId,
        public readonly float $balance,
        public readonly string $currency = 'COP'
    ) {
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'document' => $this->document,
            'wallet_id' => $this->walletId,
            'balance' => $this->balance,
            'currency' => $this->currency
        ];
    }
}
