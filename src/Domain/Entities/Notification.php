<?php

namespace VirtualBalance\Domain\Entities;

class Notification
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $title,
        public readonly string $message,
        public readonly bool $isRead,
        public readonly string $createdAt,
        public readonly ?string $updatedAt = null
    ) {}
}
