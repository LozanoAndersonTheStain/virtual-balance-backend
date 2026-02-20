<?php

namespace VirtualBalance\Infrastructure\Persistence\Repositories;

use PDO;
use VirtualBalance\Domain\Entities\Transaction;
use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\ValueObjects\TransactionStatus;
use VirtualBalance\Domain\Repositories\TransactionRepositoryInterface;
use VirtualBalance\Infrastructure\Persistence\Database\Connection;
use VirtualBalance\Shared\Utils\Logger;
use DateTime;

class MySQLTransactionRepository implements TransactionRepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    public function save(Transaction $transaction): Transaction
    {
        try {
            $sql = "INSERT INTO transactions 
                    (wallet_id, type, amount, status, session_id, token, external_reference, created_at, updated_at) 
                    VALUES (:wallet_id, :type, :amount, :status, :session_id, :token, :external_reference, :created_at, :updated_at)";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                'wallet_id' => $transaction->getWalletId(),
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount()->getAmount(),
                'status' => $transaction->getStatus()->getValue(),
                'session_id' => $transaction->getSessionId(),
                'token' => $transaction->getToken(),
                'external_reference' => $transaction->getExternalReference(),
                'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $transaction->getUpdatedAt()->format('Y-m-d H:i:s')
            ]);

            $transactionId = (int) $this->connection->lastInsertId();

            Logger::info('Transacción creada', [
                'transaction_id' => $transactionId,
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount()->getAmount()
            ]);

            return new Transaction(
                walletId: $transaction->getWalletId(),
                type: $transaction->getType(),
                amount: $transaction->getAmount(),
                status: $transaction->getStatus(),
                sessionId: $transaction->getSessionId(),
                token: $transaction->getToken(),
                externalReference: $transaction->getExternalReference(),
                id: $transactionId,
                createdAt: $transaction->getCreatedAt(),
                updatedAt: $transaction->getUpdatedAt()
            );
        } catch (\PDOException $e) {
            Logger::error('Error al guardar transacción', ['error' => $e->getMessage()]);
            throw new \RuntimeException("Error al guardar transacción: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?Transaction
    {
        try {
            $sql = "SELECT * FROM transactions WHERE id = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar transacción por ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar transacción: " . $e->getMessage());
        }
    }

    public function findByToken(string $token): ?Transaction
    {
        try {
            $sql = "SELECT * FROM transactions WHERE token = :token LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['token' => $token]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar transacción por token', ['error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar transacción: " . $e->getMessage());
        }
    }

    public function findBySessionId(string $sessionId): ?Transaction
    {
        try {
            $sql = "SELECT * FROM transactions WHERE session_id = :session_id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['session_id' => $sessionId]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar transacción por session_id', ['error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar transacción: " . $e->getMessage());
        }
    }

    public function findPendingByWalletId(int $walletId): array
    {
        try {
            $sql = "SELECT * FROM transactions 
                    WHERE wallet_id = :wallet_id AND status = 'PENDING' 
                    ORDER BY created_at DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['wallet_id' => $walletId]);
            
            $results = $stmt->fetchAll();
            
            return array_map(fn($data) => $this->hydrate($data), $results);
        } catch (\PDOException $e) {
            Logger::error('Error al buscar transacciones pendientes', ['wallet_id' => $walletId, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar transacciones: " . $e->getMessage());
        }
    }

    public function update(Transaction $transaction): bool
    {
        try {
            $sql = "UPDATE transactions 
                    SET status = :status, 
                        session_id = :session_id, 
                        token = :token, 
                        external_reference = :external_reference,
                        updated_at = :updated_at 
                    WHERE id = :id";

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([
                'status' => $transaction->getStatus()->getValue(),
                'session_id' => $transaction->getSessionId(),
                'token' => $transaction->getToken(),
                'external_reference' => $transaction->getExternalReference(),
                'updated_at' => $transaction->getUpdatedAt()->format('Y-m-d H:i:s'),
                'id' => $transaction->getId()
            ]);

            Logger::info('Transacción actualizada', [
                'transaction_id' => $transaction->getId(),
                'new_status' => $transaction->getStatus()->getValue()
            ]);

            return $result;
        } catch (\PDOException $e) {
            Logger::error('Error al actualizar transacción', ['transaction_id' => $transaction->getId(), 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al actualizar transacción: " . $e->getMessage());
        }
    }

    /**
     * Hidrata un array a una entidad Transaction
     */
    private function hydrate(array $data): Transaction
    {
        return new Transaction(
            walletId: (int) $data['wallet_id'],
            type: $data['type'],
            amount: new Balance((float) $data['amount']),
            status: new TransactionStatus($data['status']),
            sessionId: $data['session_id'],
            token: $data['token'],
            externalReference: $data['external_reference'],
            id: (int) $data['id'],
            createdAt: new DateTime($data['created_at']),
            updatedAt: new DateTime($data['updated_at'])
        );
    }
}
