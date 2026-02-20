<?php

namespace VirtualBalance\Infrastructure\Persistence\Database;

use PDO;
use PDOException;
use VirtualBalance\Shared\Utils\Logger;

class Connection
{
    private static ?PDO $instance = null;

    /**
     * Obtiene instancia singleton de PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $dbname = $_ENV['DB_NAME'] ?? 'virtual_balance';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]);

                Logger::info('Conexión a base de datos establecida', [
                    'host' => $host,
                    'database' => $dbname
                ]);
            } catch (PDOException $e) {
                Logger::critical('Error de conexión a base de datos', [
                    'error' => $e->getMessage()
                ]);
                throw new \RuntimeException("Error de conexión a base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Cierra la conexión
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
