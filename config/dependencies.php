<?php

use DI\Container;
use VirtualBalance\Domain\Repositories\UserRepositoryInterface;
use VirtualBalance\Domain\Repositories\WalletRepositoryInterface;
use VirtualBalance\Domain\Repositories\TransactionRepositoryInterface;
use VirtualBalance\Infrastructure\Persistence\Repositories\MySQLUserRepository;
use VirtualBalance\Infrastructure\Persistence\Repositories\MySQLWalletRepository;
use VirtualBalance\Infrastructure\Persistence\Repositories\MySQLTransactionRepository;
use VirtualBalance\Application\UseCases\RegisterUser\RegisterUserUseCase;
use VirtualBalance\Application\UseCases\CheckBalance\CheckBalanceUseCase;
use VirtualBalance\Application\UseCases\RechargeWallet\RechargeWalletUseCase;
use VirtualBalance\Application\UseCases\MakePayment\MakePaymentUseCase;
use VirtualBalance\Application\UseCases\ConfirmPayment\ConfirmPaymentUseCase;
use VirtualBalance\Infrastructure\Http\Controllers\HealthController;
use VirtualBalance\Infrastructure\Http\Controllers\UserController;
use VirtualBalance\Infrastructure\Http\Controllers\TransactionController;

return function (Container $container) {
    
    // ========================================
    // REPOSITORIES (Infraestructura)
    // ========================================
    
    $container->set(UserRepositoryInterface::class, function () {
        return new MySQLUserRepository();
    });

    $container->set(WalletRepositoryInterface::class, function () {
        return new MySQLWalletRepository();
    });

    $container->set(TransactionRepositoryInterface::class, function () {
        return new MySQLTransactionRepository();
    });

    // ========================================
    // USE CASES (Aplicación)
    // ========================================
    
    $container->set(RegisterUserUseCase::class, function (Container $c) {
        return new RegisterUserUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class)
        );
    });

    $container->set(CheckBalanceUseCase::class, function (Container $c) {
        return new CheckBalanceUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class)
        );
    });

    $container->set(RechargeWalletUseCase::class, function (Container $c) {
        return new RechargeWalletUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(TransactionRepositoryInterface::class)
        );
    });

    $container->set(MakePaymentUseCase::class, function (Container $c) {
        return new MakePaymentUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(TransactionRepositoryInterface::class)
        );
    });

    $container->set(ConfirmPaymentUseCase::class, function (Container $c) {
        return new ConfirmPaymentUseCase(
            $c->get(TransactionRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class)
        );
    });

    // ========================================
    // CONTROLLERS (Infraestructura HTTP)
    // ========================================
    
    $container->set(HealthController::class, function () {
        return new HealthController();
    });

    $container->set(UserController::class, function (Container $c) {
        return new UserController(
            $c->get(RegisterUserUseCase::class),
            $c->get(CheckBalanceUseCase::class)
        );
    });

    $container->set(TransactionController::class, function (Container $c) {
        return new TransactionController(
            $c->get(RechargeWalletUseCase::class),
            $c->get(MakePaymentUseCase::class),
            $c->get(ConfirmPaymentUseCase::class)
        );
    });
};
