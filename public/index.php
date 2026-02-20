<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\Container;
use Dotenv\Dotenv;
use VirtualBalance\Infrastructure\Http\Middleware\CorsMiddleware;

// Cargar autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Crear contenedor de dependencias
$container = new Container();

// Configurar dependencias
$dependencies = require __DIR__ . '/../config/dependencies.php';
$dependencies($container);

// Crear aplicación Slim
AppFactory::setContainer($container);
$app = AppFactory::create();

// Agregar middleware de parsing del body
$app->addBodyParsingMiddleware();

// Agregar middleware de routing
$app->addRoutingMiddleware();

// Agregar CORS Middleware DESPUÉS del routing para que se ejecute ANTES
$app->add(CorsMiddleware::class);

// Configurar error middleware
$displayErrorDetails = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
$logErrors = true;
$logErrorDetails = true;

$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

// Cargar rutas
$routes = require __DIR__ . '/../src/Infrastructure/Http/Routes/api.php';
$routes($app);

// Ejecutar aplicación
$app->run();
