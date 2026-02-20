<?php

namespace VirtualBalance\Domain\ValueObjects;

use InvalidArgumentException;

class TransactionStatus
{
    public const PENDING = 'PENDING';
    public const COMPLETED = 'COMPLETED';
    public const FAILED = 'FAILED';

    private const VALID_STATUSES = [
        self::PENDING,
        self::COMPLETED,
        self::FAILED
    ];

    private string $value;

    public function __construct(string $status)
    {
        $status = strtoupper($status);
        
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Estado inválido: {$status}");
        }
        
        $this->value = $status;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}