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
use VirtualBalance\Application\UseCases\Notification\CreateNotificationUseCase;
use VirtualBalance\Domain\Repositories\NotificationRepositoryInterface;
use VirtualBalance\Application\UseCases\ConfirmPayment\ConfirmPaymentUseCase;
use VirtualBalance\Infrastructure\Http\Controllers\HealthController;
use VirtualBalance\Infrastructure\Http\Controllers\UserController;
use VirtualBalance\Infrastructure\Http\Controllers\TransactionController;
use VirtualBalance\Infrastructure\Http\Controllers\NotificationController;
use VirtualBalance\Application\UseCases\Notification\GetNotificationUseCase;
use VirtualBalance\Application\UseCases\Notification\MarkNotificationAsReadUseCase;
use VirtualBalance\Application\UseCases\Notification\DeleteNotificationsUseCase;

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

    $container->set(RegisterUserUseCase::class, function(Container $c) {
        return new RegisterUserUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(CreateNotificationUseCase::class)
        );
    });

    // Notificaciones
    $container->set(NotificationRepositoryInterface::class, function () {
        return new \VirtualBalance\Infrastructure\Persistence\Repositories\MySQLNotificationRepository();
    });
    $container->set(CreateNotificationUseCase::class, function (Container $c) {
        return new CreateNotificationUseCase(
            $c->get(NotificationRepositoryInterface::class)
        );
    });

    $container->set(GetNotificationUseCase::class, function (Container $c) {
        return new GetNotificationUseCase(
            $c->get(\VirtualBalance\Domain\Repositories\NotificationRepositoryInterface::class)
        );
    });
    $container->set(MarkNotificationAsReadUseCase::class, function (Container $c) {
        return new MarkNotificationAsReadUseCase(
            $c->get(\VirtualBalance\Domain\Repositories\NotificationRepositoryInterface::class)
        );
    });
    $container->set(DeleteNotificationsUseCase::class, function (Container $c) {
        return new DeleteNotificationsUseCase(
            $c->get(\VirtualBalance\Domain\Repositories\NotificationRepositoryInterface::class)
        );
    });

    $container->set(CheckBalanceUseCase::class, function (Container $c) {
        return new CheckBalanceUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class)

        );
    });

    $container->set(RechargeWalletUseCase::class, function(Container $c) {
        return new RechargeWalletUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(TransactionRepositoryInterface::class),
            $c->get(CreateNotificationUseCase::class)
        );
    });

    $container->set(MakePaymentUseCase::class, function(Container $c) {
        return new MakePaymentUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(TransactionRepositoryInterface::class),
            $c->get(CreateNotificationUseCase::class)
        );
    });

    $container->set(\VirtualBalance\Application\UseCases\ConfirmPayment\ConfirmPaymentUseCase::class, function(Container $c) {
        return new \VirtualBalance\Application\UseCases\ConfirmPayment\ConfirmPaymentUseCase(
            $c->get(TransactionRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(CreateNotificationUseCase::class)
        );
    });

    $container->set(ConfirmPaymentUseCase::class, function (Container $c) {
        return new ConfirmPaymentUseCase(
            $c->get(TransactionRepositoryInterface::class),
            $c->get(WalletRepositoryInterface::class),
            $c->get(CreateNotificationUseCase::class)
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

    $container->set(NotificationController::class, function (Container $c) {
        return new NotificationController(
            $c->get(GetNotificationUseCase::class),
            $c->get(MarkNotificationAsReadUseCase::class),
            $c->get(DeleteNotificationsUseCase::class),
            $c->get(UserRepositoryInterface::class)
        );
    });
};
