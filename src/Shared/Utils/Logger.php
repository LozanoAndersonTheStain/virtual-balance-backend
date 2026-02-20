<?php

namespace VirtualBalance\Shared\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private static ?MonologLogger $instance = null;

    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            $logger = new MonologLogger('virtual-balance');
            
            $logPath = __DIR__ . '/../../../logs/app.log';
            $logLevel = self::getLogLevel($_ENV['LOG_LEVEL'] ?? 'debug');

            // Handler con rotación diaria
            $handler = new RotatingFileHandler($logPath, 30, $logLevel);
            
            // Formato personalizado
            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context%\n",
                "Y-m-d H:i:s"
            );
            $handler->setFormatter($formatter);
            
            $logger->pushHandler($handler);

            // En desarrollo, también log a consola
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                $consoleHandler = new StreamHandler('php://stdout', $logLevel);
                $consoleHandler->setFormatter($formatter);
                $logger->pushHandler($consoleHandler);
            }

            self::$instance = $logger;
        }

        return self::$instance;
    }

    private static function getLogLevel(string $level): int
    {
        return match (strtolower($level)) {
            'debug' => MonologLogger::DEBUG,
            'info' => MonologLogger::INFO,
            'notice' => MonologLogger::NOTICE,
            'warning' => MonologLogger::WARNING,
            'error' => MonologLogger::ERROR,
            'critical' => MonologLogger::CRITICAL,
            'alert' => MonologLogger::ALERT,
            'emergency' => MonologLogger::EMERGENCY,
            default => MonologLogger::DEBUG,
        };
    }

    // Métodos estáticos de conveniencia
    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::getInstance()->critical($message, $context);
    }
}
