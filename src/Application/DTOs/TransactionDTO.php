<?php

namespace VirtualBalance\Application\DTOs;

class TransactionDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $walletId,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $status,
        public readonly ?string $sessionId = null,
        public readonly ?string $token = null,
        public readonly ?string $externalReference = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->walletId,
            'type' => $this->type,
            'amount' => $this->amount,
            'status' => $this->status,
            'session_id' => $this->sessionId,
            'token' => $this->token,
            'external_reference' => $this->externalReference,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
