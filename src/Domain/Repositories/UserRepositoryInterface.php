<?php

namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function save(User $user): User;
    public function findById(int $id): ?User;
    public function findByDocument(string $document): ?User;
    public function findByEmail(string $email): ?User;
    public function existsByDocument(string $document): bool;
    public function existsByEmail(string $email): bool;
}