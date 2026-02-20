<?php

namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): Transaction;
    public function findById(int $id): ?Transaction;
    public function findByToken(string $token): ?Transaction;
    public function findBySessionId(string $sessionId): ?Transaction;
    public function findPendingByWalletId(int $walletId): array;
    public function update(Transaction $transaction): bool;
}