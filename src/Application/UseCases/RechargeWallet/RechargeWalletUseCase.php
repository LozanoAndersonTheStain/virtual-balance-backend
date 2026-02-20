<?php

namespace VirtualBalance\Application\UseCases\RechargeWallet;

use VirtualBalance\Domain\Entities\Transaction;
use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\ValueObjects\TransactionStatus;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Repositories\TransactionRepositoryInterface;
use VirtualBalance\Domain\Exceptions\UserNotFoundException;
use VirtualBalance\Domain\Exceptions\WalletNotFoundException;
use VirtualBalance\Application\DTOs\PaymentResponseDTO;
use InvalidArgumentException;

class RechargeWalletUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {
    }

    /**
     * Inicia una recarga de billetera (crea transacción pendiente)
     * 
     * @param RechargeWalletRequest $request
     * @return PaymentResponseDTO
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws InvalidArgumentException
     */
    public function execute(RechargeWalletRequest $request): PaymentResponseDTO
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

        // Crear transacción pendiente
        $transaction = new Transaction(
            walletId: $wallet->getId(),
            type: 'RECHARGE',
            amount: new Balance($request->amount),
            status: TransactionStatus::pending()
        );

        // Guardar transacción
        $savedTransaction = $this->transactionRepository->save($transaction);

        // Simular generación de sessionId y token para pasarela de pagos
        $sessionId = $this->generateSessionId();
        $token = $this->generatePaymentToken();

        // Actualizar transacción con token y sessionId
        $savedTransaction->setPaymentToken($token, $sessionId);
        $this->transactionRepository->update($savedTransaction);

        // Retornar respuesta
        return new PaymentResponseDTO(
            success: true,
            sessionId: $sessionId,
            token: $token,
            amount: $request->amount,
            status: TransactionStatus::PENDING,
            message: 'Transacción creada. Usa el token para confirmar el pago.',
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
