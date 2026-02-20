<?php

namespace VirtualBalance\Domain\Entities;

use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\Exceptions\InsufficientBalanceException;
use DateTime;

class Wallet
{
    private ?int $id;
    private int $userId;
    private Balance $balance;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $userId,
        ?Balance $balance = null,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->balance = $balance ?? new Balance(0.0);
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getBalance(): Balance
    {
        return $this->balance;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Métodos de dominio
    public function recharge(Balance $amount): void
    {
        $this->balance = $this->balance->add($amount);
        $this->updatedAt = new DateTime();
    }

    public function debit(Balance $amount): void
    {
        if (!$this->balance->isGreaterOrEqualThan($amount)) {
            throw new InsufficientBalanceException(
                $amount->getAmount(),
                $this->balance->getAmount()
            );
        }

        $this->balance = $this->balance->subtract($amount);
        $this->updatedAt = new DateTime();
    }

    public function hasBalance(Balance $amount): bool
    {
        return $this->balance->isGreaterOrEqualThan($amount);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'balance' => $this->balance->getAmount(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
