<?php

namespace VirtualBalance\Infrastructure\Persistence\Repositories;

use PDO;
use VirtualBalance\Domain\Entities\User;
use VirtualBalance\Domain\ValueObjects\Email;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Infrastructure\Persistence\Database\Connection;
use VirtualBalance\Shared\Utils\Logger;
use DateTime;

class MySQLUserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    public function save(User $user): User
    {
        try {
            $sql = "INSERT INTO users (document, name, email, phone, created_at, updated_at) 
                    VALUES (:document, :name, :email, :phone, :created_at, :updated_at)";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                'document' => $user->getDocument(),
                'name' => $user->getName(),
                'email' => $user->getEmail()->getValue(),
                'phone' => $user->getPhone(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
            ]);

            $userId = (int) $this->connection->lastInsertId();

            Logger::info('Usuario creado', ['user_id' => $userId, 'document' => $user->getDocument()]);

            return new User(
                document: $user->getDocument(),
                name: $user->getName(),
                email: $user->getEmail(),
                phone: $user->getPhone(),
                id: $userId,
                createdAt: $user->getCreatedAt(),
                updatedAt: $user->getUpdatedAt()
            );
        } catch (\PDOException $e) {
            Logger::error('Error al guardar usuario', ['error' => $e->getMessage()]);
            throw new \RuntimeException("Error al guardar usuario: " . $e->getMessage());
        }
    }

    public function findById(int $id): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar usuario por ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar usuario: " . $e->getMessage());
        }
    }

    public function findByDocument(string $document): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE document = :document LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['document' => $document]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar usuario por documento', ['document' => $document, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar usuario: " . $e->getMessage());
        }
    }

    public function findByEmail(string $email): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            $data = $stmt->fetch();
            
            return $data ? $this->hydrate($data) : null;
        } catch (\PDOException $e) {
            Logger::error('Error al buscar usuario por email', ['email' => $email, 'error' => $e->getMessage()]);
            throw new \RuntimeException("Error al buscar usuario: " . $e->getMessage());
        }
    }

    public function existsByDocument(string $document): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE document = :document";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['document' => $document]);
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (\PDOException $e) {
            Logger::error('Error al verificar existencia de documento', ['document' => $document, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function existsByEmail(string $email): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['email' => $email]);
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (\PDOException $e) {
            Logger::error('Error al verificar existencia de email', ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Hidrata un array a una entidad User
     */
    private function hydrate(array $data): User
    {
        return new User(
            document: $data['document'],
            name: $data['name'],
            email: new Email($data['email']),
            phone: $data['phone'],
            id: (int) $data['id'],
            createdAt: new DateTime($data['created_at']),
            updatedAt: new DateTime($data['updated_at'])
        );
    }
}
