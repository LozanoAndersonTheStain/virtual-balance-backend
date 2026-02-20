<?php

namespace VirtualBalance\Application\UseCases\CheckBalance;

use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Exceptions\UserNotFoundException;
use VirtualBalance\Domain\Exceptions\WalletNotFoundException;
use VirtualBalance\Application\DTOs\BalanceResponseDTO;
use InvalidArgumentException;

class CheckBalanceUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository
    ) {
    }

    /**
     * Consulta el saldo de un usuario por su documento
     * 
     * @param CheckBalanceRequest $request
     * @return BalanceResponseDTO
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws InvalidArgumentException
     */
    public function execute(CheckBalanceRequest $request): BalanceResponseDTO
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

        // Retornar DTO con información del saldo
        return new BalanceResponseDTO(
            userId: $user->getId(),
            userName: $user->getName(),
            document: $user->getDocument(),
            walletId: $wallet->getId(),
            balance: $wallet->getBalance()->getAmount(),
            currency: 'COP'
        );
    }
}
