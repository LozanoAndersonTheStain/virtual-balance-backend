<?php

namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\Wallet;

interface WalletRepositoryInterface
{
    public function save(Wallet $wallet): Wallet;
    public function findById(int $id): ?Wallet;
    public function findByUserId(int $userId): ?Wallet;
    public function update(Wallet $wallet): bool;
}