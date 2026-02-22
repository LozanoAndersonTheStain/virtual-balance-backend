<?php

namespace VirtualBalance\Application\DTOs;

class NotificationDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $message,
        public readonly bool $isRead,
        public readonly string $timestamp
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => $this->isRead,
            'timestamp' => $this->timestamp,
        ];
    }
}
