<?php

namespace VirtualBalance\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use VirtualBalance\Application\UseCases\RechargeWallet\RechargeWalletRequest;
use VirtualBalance\Application\UseCases\RechargeWallet\RechargeWalletUseCase;
use VirtualBalance\Application\UseCases\MakePayment\MakePaymentRequest;
use VirtualBalance\Application\UseCases\MakePayment\MakePaymentUseCase;
use VirtualBalance\Application\UseCases\ConfirmPayment\ConfirmPaymentRequest;
use VirtualBalance\Application\UseCases\ConfirmPayment\ConfirmPaymentUseCase;
use VirtualBalance\Shared\Utils\ResponseFormatter;
use VirtualBalance\Shared\Utils\Logger;
use VirtualBalance\Domain\Exceptions\UserNotFoundException;
use VirtualBalance\Domain\Exceptions\InsufficientBalanceException;
use VirtualBalance\Domain\Exceptions\TransactionNotFoundException;

class TransactionController
{
    public function __construct(
        private RechargeWalletUseCase $rechargeWalletUseCase,
        private MakePaymentUseCase $makePaymentUseCase,
        private ConfirmPaymentUseCase $confirmPaymentUseCase
    ) {
    }

    /**
     * POST /api/transactions/recharge
     * Inicia una recarga de saldo
     */
    public function recharge(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            $rechargeRequest = new RechargeWalletRequest(
                document: $data['document'] ?? '',
                amount: (float) ($data['amount'] ?? 0)
            );

            $paymentDTO = $this->rechargeWalletUseCase->execute($rechargeRequest);

            Logger::info('Recarga iniciada', [
                'document' => $data['document'] ?? '',
                'amount' => $data['amount'] ?? 0
            ]);

            return ResponseFormatter::success(
                data: $paymentDTO->toArray(),
                message: 'Recarga iniciada. Usa el token para confirmar el pago.',
                statusCode: 201
            );
        } catch (UserNotFoundException $e) {
            Logger::warning('Usuario no encontrado en recarga', ['error' => $e->getMessage()]);
            return ResponseFormatter::notFound($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos de recarga inválidos', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al iniciar recarga', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al iniciar recarga');
        }
    }

    /**
     * POST /api/transactions/payment
     * Realiza un pago
     */
    public function payment(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            $paymentRequest = new MakePaymentRequest(
                document: $data['document'] ?? '',
                amount: (float) ($data['amount'] ?? 0),
                description: $data['description'] ?? null
            );

            $paymentDTO = $this->makePaymentUseCase->execute($paymentRequest);

            Logger::info('Pago realizado', [
                'document' => $data['document'] ?? '',
                'amount' => $data['amount'] ?? 0
            ]);

            return ResponseFormatter::success(
                data: $paymentDTO->toArray(),
                message: 'Pago realizado exitosamente',
                statusCode: 201
            );
        } catch (UserNotFoundException $e) {
            Logger::warning('Usuario no encontrado en pago', ['error' => $e->getMessage()]);
            return ResponseFormatter::notFound($e->getMessage());
        } catch (InsufficientBalanceException $e) {
            Logger::warning('Saldo insuficiente', ['error' => $e->getMessage()]);
            return ResponseFormatter::error($e->getMessage(), [], 400);
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos de pago inválidos', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al realizar pago', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al realizar pago');
        }
    }

    /**
     * POST /api/transactions/confirm
     * Confirma una transacción pendiente
     */
    public function confirm(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            $confirmRequest = new ConfirmPaymentRequest(
                token: $data['token'] ?? '',
                sessionId: $data['sessionId'] ?? ''
            );

            $transactionDTO = $this->confirmPaymentUseCase->execute($confirmRequest);

            Logger::info('Transacción confirmada', [
                'transaction_id' => $transactionDTO->id,
                'status' => $transactionDTO->status
            ]);

            // Si la transacción falló, retornar como error
            if ($transactionDTO->status === 'FAILED') {
                return ResponseFormatter::error(
                    message: 'La transacción falló. El pago no pudo ser procesado por la pasarela.',
                    data: $transactionDTO->toArray(),
                    statusCode: 400
                );
            }

            return ResponseFormatter::success(
                data: $transactionDTO->toArray(),
                message: 'Transacción confirmada exitosamente'
            );
        } catch (TransactionNotFoundException $e) {
            Logger::warning('Transacción no encontrada', ['error' => $e->getMessage()]);
            return ResponseFormatter::notFound($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Datos de confirmación inválidos', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al confirmar transacción', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al confirmar transacción');
        }
    }

    /**
     * POST /api/notifications/payment
     * Webhook para recibir notificaciones de confirmación de pago desde pasarelas externas
     * 
     * Este endpoint está diseñado específicamente para ser llamado por servicios de pago
     * externos (PSE, Nequi, Bancolombia, etc.) cuando confirman una transacción.
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function notifyPayment(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();

            // Log de notificación recibida para auditoría
            Logger::info('Notificación de pago recibida', [
                'token' => $data['token'] ?? 'N/A',
                'sessionId' => $data['sessionId'] ?? 'N/A',
                'source' => $request->getHeaderLine('User-Agent') ?: 'Unknown'
            ]);

            $confirmRequest = new ConfirmPaymentRequest(
                token: $data['token'] ?? '',
                sessionId: $data['sessionId'] ?? ''
            );

            $transactionDTO = $this->confirmPaymentUseCase->execute($confirmRequest);

            Logger::info('Notificación de pago procesada', [
                'transaction_id' => $transactionDTO->id,
                'status' => $transactionDTO->status,
                'amount' => $transactionDTO->amount
            ]);

            // Si la transacción falló, retornar como error
            if ($transactionDTO->status === 'FAILED') {
                return ResponseFormatter::error(
                    message: 'Notificación recibida. La transacción fue marcada como fallida.',
                    data: $transactionDTO->toArray(),
                    statusCode: 400
                );
            }

            return ResponseFormatter::success(
                data: $transactionDTO->toArray(),
                message: 'Notificación de pago recibida y procesada exitosamente. Saldo actualizado.'
            );
        } catch (TransactionNotFoundException $e) {
            Logger::warning('Notificación con token inválido', ['error' => $e->getMessage()]);
            return ResponseFormatter::notFound($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            Logger::warning('Notificación con datos inválidos', ['error' => $e->getMessage()]);
            return ResponseFormatter::validationError([], $e->getMessage());
        } catch (\Exception $e) {
            Logger::error('Error al procesar notificación de pago', ['error' => $e->getMessage()]);
            return ResponseFormatter::serverError('Error al procesar notificación de pago');
        }
    }
}
