<?php

namespace VirtualBalance\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use VirtualBalance\Shared\Utils\ResponseFormatter;
use VirtualBalance\Shared\Utils\Logger;

class ApiKeyAuthMiddleware implements MiddlewareInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = $_ENV['API_KEY'] ?? 'your_secret_api_key_here_change_in_production';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Obtener API Key del header
        $headerApiKey = $request->getHeaderLine('X-API-Key');

        // Si no hay API Key en el header, verificar query string (solo para development)
        if (empty($headerApiKey)) {
            $queryParams = $request->getQueryParams();
            $headerApiKey = $queryParams['api_key'] ?? '';
        }

        // Validar API Key
        if ($headerApiKey !== $this->apiKey) {
            Logger::warning('Intento de acceso con API Key inválida', [
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                'path' => $request->getUri()->getPath()
            ]);

            return ResponseFormatter::unauthorized('API Key inválida o ausente');
        }

        // Continuar con la petición
        return $handler->handle($request);
    }
}
