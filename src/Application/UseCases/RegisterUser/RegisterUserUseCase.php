<?php

namespace VirtualBalance\Application\UseCases\RegisterUser;

use VirtualBalance\Domain\Entities\User;
use VirtualBalance\Domain\Entities\Wallet;
use VirtualBalance\Domain\ValueObjects\Email;
use VirtualBalance\Domain\ValueObjects\Balance;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Exceptions\DuplicateUserException;
use VirtualBalance\Application\DTOs\UserDTO;
use InvalidArgumentException;

class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository
    ) {
    }

    /**
     * Registra un nuevo usuario con su billetera
     * 
     * @param RegisterUserRequest $request
     * @return UserDTO
     * @throws DuplicateUserException
     * @throws InvalidArgumentException
     */
    public function execute(RegisterUserRequest $request): UserDTO
    {
        // Validar request
        $errors = $request->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException('Datos inválidos: ' . json_encode($errors));
        }

        // Verificar que no exista usuario con mismo documento
        if ($this->userRepository->existsByDocument($request->document)) {
            throw new DuplicateUserException('documento', $request->document);
        }

        // Verificar que no exista usuario con mismo email
        if ($this->userRepository->existsByEmail($request->email)) {
            throw new DuplicateUserException('email', $request->email);
        }

        // Crear usuario
        $user = new User(
            document: $request->document,
            name: $request->name,
            email: new Email($request->email),
            phone: $request->phone
        );

        // Guardar usuario
        $savedUser = $this->userRepository->save($user);

        // Crear billetera para el usuario con saldo inicial 0
        $wallet = new Wallet(
            userId: $savedUser->getId(),
            balance: new Balance(0.0)
        );

        $this->walletRepository->save($wallet);

        // Retornar DTO
        return new UserDTO(
            id: $savedUser->getId(),
            document: $savedUser->getDocument(),
            name: $savedUser->getName(),
            email: $savedUser->getEmail()->getValue(),
            phone: $savedUser->getPhone(),
            createdAt: $savedUser->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $savedUser->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
