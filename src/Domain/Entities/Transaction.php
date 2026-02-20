<?php

namespace VirtualBalance\Domain\Entities;

use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\ValueObjects\TransactionStatus;
use DateTime;

class Transaction
{
    private ?int $id;
    private int $walletId;
    private string $type; // 'RECHARGE' o 'PAYMENT'
    private Balance $amount;
    private TransactionStatus $status;
    private ?string $sessionId;
    private ?string $token;
    private ?string $externalReference;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $walletId,
        string $type,
        Balance $amount,
        ?TransactionStatus $status = null,
        ?string $sessionId = null,
        ?string $token = null,
        ?string $externalReference = null,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->walletId = $walletId;
        $this->type = strtoupper($type);
        $this->amount = $amount;
        $this->status = $status ?? TransactionStatus::pending();
        $this->sessionId = $sessionId;
        $this->token = $token;
        $this->externalReference = $externalReference;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): Balance
    {
        return $this->amount;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExternalReference(): ?string
    {
        return $this->externalReference;
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
    public function markAsCompleted(): void
    {
        $this->status = TransactionStatus::completed();
        $this->updatedAt = new DateTime();
    }

    public function markAsFailed(): void
    {
        $this->status = TransactionStatus::failed();
        $this->updatedAt = new DateTime();
    }

    public function setPaymentToken(string $token, string $sessionId): void
    {
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->updatedAt = new DateTime();
    }

    public function isRecharge(): bool
    {
        return $this->type === 'RECHARGE';
    }

    public function isPayment(): bool
    {
        return $this->type === 'PAYMENT';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->walletId,
            'type' => $this->type,
            'amount' => $this->amount->getAmount(),
            'status' => $this->status->getValue(),
            'session_id' => $this->sessionId,
            'token' => $this->token,
            'external_reference' => $this->externalReference,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}