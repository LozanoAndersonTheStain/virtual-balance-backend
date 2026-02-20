<?php

namespace VirtualBalance\Application\UseCases\ConfirmPayment;

use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Repositories\TransactionRepositoryInterface;
use VirtualBalance\Domain\Exceptions\TransactionNotFoundException;
use VirtualBalance\Domain\Exceptions\WalletNotFoundException;
use VirtualBalance\Application\DTOs\TransactionDTO;
use InvalidArgumentException;
use Exception;

class ConfirmPaymentUseCase
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private WalletRepositoryInterface $walletRepository
    ) {
    }

    /**
     * Confirma un pago pendiente (simula respuesta de pasarela)
     * 
     * @param ConfirmPaymentRequest $request
     * @return TransactionDTO
     * @throws TransactionNotFoundException
     * @throws WalletNotFoundException
     * @throws InvalidArgumentException
     */
    public function execute(ConfirmPaymentRequest $request): TransactionDTO
    {
        // Validar request
        $errors = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException('Datos inválidos: ' . json_encode($errors));
        }

        // Buscar transacción por token
        $transaction = $this->transactionRepository->findByToken($request->token);
        if (!$transaction) {
            throw new TransactionNotFoundException($request->token);
        }

        // Validar que la transacción esté pendiente
        if (!$transaction->getStatus()->isPending()) {
            throw new InvalidArgumentException(
                'La transacción ya fue procesada con estado: ' . $transaction->getStatus()->getValue()
            );
        }

        // Validar sessionId
        if ($transaction->getSessionId() !== $request->sessionId) {
            throw new InvalidArgumentException('Session ID inválido');
        }

        // Buscar billetera
        $wallet = $this->walletRepository->findById($transaction->getWalletId());
        if (!$wallet) {
            throw new WalletNotFoundException($transaction->getWalletId());
        }

        // Simular validación con pasarela de pagos (80% éxito)
        $paymentSuccessful = $this->simulatePaymentGatewayResponse();

        if ($paymentSuccessful) {
            // Acreditar saldo a la billetera (solo para recargas)
            if ($transaction->isRecharge()) {
                $wallet->recharge($transaction->getAmount());
                $this->walletRepository->update($wallet);
            }

            // Marcar transacción como completada
            $transaction->markAsCompleted();
        } else {
            // Marcar transacción como fallida
            $transaction->markAsFailed();
        }

        // Actualizar transacción
        $this->transactionRepository->update($transaction);

        // Retornar DTO
        return new TransactionDTO(
            id: $transaction->getId(),
            walletId: $transaction->getWalletId(),
            type: $transaction->getType(),
            amount: $transaction->getAmount()->getAmount(),
            status: $transaction->getStatus()->getValue(),
            sessionId: $transaction->getSessionId(),
            token: $transaction->getToken(),
            externalReference: $transaction->getExternalReference(),
            createdAt: $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $transaction->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    /**
     * Simula respuesta de pasarela de pagos
     * En producción, aquí se haría una llamada HTTP real
     */
    private function simulatePaymentGatewayResponse(): bool
    {
        // 80% de probabilidad de éxito
        $successRate = (float) ($_ENV['PAYMENT_SUCCESS_RATE'] ?? 0.8);
        return (mt_rand() / mt_getrandmax()) < $successRate;
    }
}
