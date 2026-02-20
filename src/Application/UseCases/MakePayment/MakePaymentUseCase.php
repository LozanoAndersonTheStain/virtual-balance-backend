<?php

namespace VirtualBalance\Application\UseCases\MakePayment;

use VirtualBalance\Domain\Entities\Transaction;
use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\ValueObjects\TransactionStatus;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Repositories\TransactionRepositoryInterface;
use VirtualBalance\Domain\Exceptions\UserNotFoundException;
use VirtualBalance\Domain\Exceptions\WalletNotFoundException;
use VirtualBalance\Domain\Exceptions\InsufficientBalanceException;
use VirtualBalance\Application\DTOs\PaymentResponseDTO;
use InvalidArgumentException;

class MakePaymentUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {
    }

    /**
     * Realiza un pago descontando el saldo de la billetera
     * 
     * @param MakePaymentRequest $request
     * @return PaymentResponseDTO
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     */
    public function execute(MakePaymentRequest $request): PaymentResponseDTO
    {
        // Validar request
        $errors = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException('Datos inválidos: ' . json_encode($errors));
        }

        // Buscar usuario por documento
        $user = $this->userRepository->findByDocument($request->document);
        if (!$user) {
            throw new UserNotFoundException($request->document);
        }

        // Buscar billetera del usuario
        $wallet = $this->walletRepository->findByUserId($user->getId());
        if (!$wallet) {
            throw new WalletNotFoundException($user->getId());
        }

        // Verificar saldo suficiente
        $paymentAmount = new Balance($request->amount);
        if (!$wallet->hasBalance($paymentAmount)) {
            throw new InsufficientBalanceException(
                $request->amount,
                $wallet->getBalance()->getAmount()
            );
        }

        // Debitar saldo de la billetera
        $wallet->debit($paymentAmount);
        $this->walletRepository->update($wallet);

        // Crear transacción completada
        $transaction = new Transaction(
            walletId: $wallet->getId(),
            type: 'PAYMENT',
            amount: $paymentAmount,
            status: TransactionStatus::completed()
        );

        // Guardar transacción
        $savedTransaction = $this->transactionRepository->save($transaction);

        // Generar token de confirmación
        $sessionId = $this->generateSessionId();
        $token = $this->generatePaymentToken();

        // Actualizar transacción con token
        $savedTransaction->setPaymentToken($token, $sessionId);
        $this->transactionRepository->update($savedTransaction);

        // Retornar respuesta
        return new PaymentResponseDTO(
            success: true,
            sessionId: $sessionId,
            token: $token,
            amount: $request->amount,
            status: TransactionStatus::COMPLETED,
            message: 'Pago realizado exitosamente.',
            transactionId: $savedTransaction->getId()
        );
    }

    private function generateSessionId(): string
    {
        return 'sess_' . bin2hex(random_bytes(16));
    }

    private function generatePaymentToken(): string
    {
        return 'tok_' . bin2hex(random_bytes(20));
    }
}
