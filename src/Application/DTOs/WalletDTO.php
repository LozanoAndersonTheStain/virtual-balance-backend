<?php

namespace VirtualBalance\Application\DTOs;

class WalletDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly float $balance,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'balance' => $this->balance,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
