<?php

namespace VirtualBalance\Shared\Utils;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class ResponseFormatter
{
    /**
     * Formatea respuesta de éxito
     */
    public static function success(
        array $data = [],
        string $message = 'Éxito',
        int $statusCode = 200
    ): ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * Formatea respuesta de error
     */
    public static function error(
        string $message = 'Error',
        array $errors = [],
        int $statusCode = 400,
        array $data = []
    ): ResponseInterface {
        $response = new Response();
        $payload = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        $response->getBody()->write(
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * Formatea respuesta de validación
     */
    public static function validationError(
        array $errors,
        string $message = 'Error de validación'
    ): ResponseInterface {
        return self::error($message, $errors, 422);
    }

    /**
     * Formatea respuesta de no encontrado
     */
    public static function notFound(string $message = 'Recurso no encontrado'): ResponseInterface
    {
        return self::error($message, [], 404);
    }

    /**
     * Formatea respuesta de no autorizado
     */
    public static function unauthorized(string $message = 'No autorizado'): ResponseInterface
    {
        return self::error($message, [], 401);
    }

    /**
     * Formatea respuesta de conflicto
     */
    public static function conflict(string $message = 'Conflicto'): ResponseInterface
    {
        return self::error($message, [], 409);
    }

    /**
     * Formatea respuesta de error del servidor
     */
    public static function serverError(
        string $message = 'Error interno del servidor'
    ): ResponseInterface {
        return self::error($message, [], 500);
    }
}
