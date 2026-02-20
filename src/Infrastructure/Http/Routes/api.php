<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use VirtualBalance\Infrastructure\Http\Controllers\HealthController;
use VirtualBalance\Infrastructure\Http\Controllers\UserController;
use VirtualBalance\Infrastructure\Http\Controllers\TransactionController;
use VirtualBalance\Infrastructure\Http\Middleware\ApiKeyAuthMiddleware;

return function (App $app) {
    // Health Check (público, sin autenticación)
    $app->get('/api/health', [HealthController::class, 'check']);

    // Grupo de rutas API protegidas con API Key
    $app->group('/api', function (RouteCollectorProxy $group) {
        
        // Rutas de usuarios
        $group->group('/users', function (RouteCollectorProxy $userGroup) {
            $userGroup->post('/register', [UserController::class, 'register']);
            $userGroup->get('/{document}/balance', [UserController::class, 'getBalance']);
        });

        // Rutas de transacciones
        $group->group('/transactions', function (RouteCollectorProxy $txGroup) {
            $txGroup->post('/recharge', [TransactionController::class, 'recharge']);
            $txGroup->post('/payment', [TransactionController::class, 'payment']);
            $txGroup->post('/confirm', [TransactionController::class, 'confirm']);
        });

        // Rutas de notificaciones (Webhooks)
        $group->group('/notifications', function (RouteCollectorProxy $notifGroup) {
            // Webhook para confirmación de pagos desde pasarelas externas
            $notifGroup->post('/payment', [TransactionController::class, 'notifyPayment']);
        });

    })->add(ApiKeyAuthMiddleware::class);

    // Ruta raíz
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'service' => 'Virtual Balance API',
            'version' => '1.0.0',
            'status' => 'running',
            'documentation' => '/api/health',
            'test_interface' => '/test.html'
        ], JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
