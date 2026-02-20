<?php

namespace VirtualBalance\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VirtualBalance\Shared\Utils\ResponseFormatter;
use VirtualBalance\Infrastructure\Persistence\Database\Connection;

class HealthController
{
    /**
     * GET /api/health
     * Verifica el estado de la API
     */
    public function check(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $health = [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'service' => 'Virtual Balance API',
            'version' => '1.0.0'
        ];

        // Verificar conexión a base de datos
        try {
            Connection::getInstance();
            $health['database'] = 'connected';
        } catch (\Exception $e) {
            $health['database'] = 'disconnected';
            $health['status'] = 'degraded';
        }

        return ResponseFormatter::success(
            data: $health,
            message: 'API funcionando correctamente'
        );
    }
}
