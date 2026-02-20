<?php

namespace VirtualBalance\Infrastructure\Persistence\Repositories;

use PDO;
use VirtualBalance\Domain\Entities\Wallet;
use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Infrastructure\Persistence\Database\Connection;
use VirtualBalance\Shared\Utils\Logger;
use DateTime;

class MySQLWalletRepository implements WalletRepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    public function save(Wallet $wallet): Wallet
    {
        try {
            $sql = "INSERT INTO wallets (user_id, balance, created_at, updated_at) 
                    VALUES (:user_id, :balance, :created_at, :updated_at)";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                'user_id' => $wallet->getUserId(),
                'balance' => $wallet->getBalance()->getAmount(),
                'created_at' => $wallet->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $wallet->getUpdatedAt()->format('Y-m-d H:i:s')
            ]);

            $walletId = (int) $this->connection->lastInsertId();

            Logger::info('Billetera creada', ['wallet_id' => $walletId, 'user_id' => $wallet->getUserId()]);

            return new Wallet(
                userId: $wallet->getUserId(),
                balance: $wallet->getBalance(),
                id: $walletId,
                createdAt: $wallet->getCreatedAt(),
                updatedAt: $wallet->getUpdatedAt()
            );
        } catch (\PDOException $e) {
            Logger::error('Error al guardar billetera', ['error' => $e->getMessage()]);
            throw new \RuntimeException("Error al guardar billetera: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?Wallet
    {
        try {
            $sql = "SELECT * FROM wallets WHERE id = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar billetera por ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar billetera: " . $e->getMessage());
        }
    }

    public function findByUserId(int $userId): ?Wallet
    {
        try {
            $sql = "SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar billetera por user_id', ['user_id' => $userId, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar billetera: " . $e->getMessage());
        }
    }

    public function update(Wallet $wallet): bool
    {
        try {
            $sql = "UPDATE wallets 
                    SET balance = :balance, updated_at = :updated_at 
                    WHERE id = :id";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([
                'balance' => $wallet->getBalance()->getAmount(),
                'updated_at' => $wallet->getUpdatedAt()->format('Y-m-d H:i:s'),
                'id' => $wallet->getId()
            ]);

            Logger::info('Billetera actualizada', [
                'wallet_id' => $wallet->getId(),
                'new_balance' => $wallet->getBalance()->getAmount()
            ]);

            return $result;
        } catch (\PDOException $e) {
            Logger::error('Error al actualizar billetera', ['wallet_id' => $wallet->getId(), 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al actualizar billetera: " . $e->getMessage());
        }
    }

    /**
     * Hidrata un array a una entidad Wallet
     */
    private function hydrate(array $data): Wallet
    {
        return new Wallet(
            userId: (int) $data['user_id'],
            balance: new Balance((float) $data['balance']),
            id: (int) $data['id'],
            createdAt: new DateTime($data['created_at']),
            updatedAt: new DateTime($data['updated_at'])
        );
    }
}
