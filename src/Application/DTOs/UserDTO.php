<?php

namespace VirtualBalance\Application\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $document,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'document' => $this->document,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
