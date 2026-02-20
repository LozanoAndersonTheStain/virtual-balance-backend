<?php

namespace VirtualBalance\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VirtualBalance\Application\UseCases\RegisterUser\RegisterUserRequest;
use VirtualBalance\Application\UseCases\RegisterUser\RegisterUserUseCase;
use VirtualBalance\Application\UseCases\CheckBalance\CheckBalanceRequest;
use VirtualBalance\Application\UseCases\CheckBalance\CheckBalanceUseCase;
use VirtualBalance\Shared\Utils\ResponseFormatter;
use VirtualBalance\Shared\Utils\Logger;
use VirtualBalance\Domain\Exceptions\DuplicateUserException;
use VirtualBalance\Domain\Exceptions\UserNotFoundException;

class UserController
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase,
        private CheckBalanceUseCase $checkBalanceUseCase
    ) {
    }

    /**
     * POST /api/users/register
     * Registra un nuevo usuario
     */
    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            $userRequest = new RegisterUserRequest(
                document: $data['document'] ?? '',
                name: $data['name'] ?? '',
                email: $data['email'] ?? '',
                phone: $data['phone'] ?? ''
            );

            $userDTO = $this->registerUserUseCase->execute($userRequest);

            Logger::info('Usuario registrado exitosamente', ['user_id' => $userDTO->id]);

            return ResponseFormatter::success(
                data: $userDTO->toArray(),
                message: 'Usuario registrado exitosamente',
                statusCode: 201
            );
        } catch (DuplicateUserException $e) {
            Logger::warning('Intento de registro duplicado', ['error' => $e->getMessage()]);
            return ResponseFormatter::conflict($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos de registro inválidos', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al registrar usuario', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al registrar usuario');
        }
    }

    /**
     * GET /api/users/{document}/balance
     * Consulta el saldo de un usuario
     */
    public function getBalance(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $document = $args['document'] ?? '';
        
        try {
            $balanceRequest = new CheckBalanceRequest(document: $document);
            $balanceDTO = $this->checkBalanceUseCase->execute($balanceRequest);

            Logger::info('Consulta de saldo exitosa', ['document' => $document]);

            return ResponseFormatter::success(
                data: $balanceDTO->toArray(),
                message: 'Saldo consultado exitosamente'
            );
        } catch (UserNotFoundException $e) {
            Logger::warning('Usuario no encontrado en consulta de saldo', ['document' => $document]);
            return ResponseFormatter::notFound('Usuario no encontrado con el documento: ' . $document);
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos inválidos en consulta de saldo', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al consultar saldo', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al consultar saldo');
        }
    }
}
