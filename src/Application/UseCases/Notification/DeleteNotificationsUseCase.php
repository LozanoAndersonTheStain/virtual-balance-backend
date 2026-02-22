<?php

namespace VirtualBalance\Application\UseCases\Notification;

use VirtualBalance\Domain\Repositories\NotificationRepositoryInterface;

class DeleteNotificationsUseCase
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function execute(int $userId): void
    {
        $this->notificationRepository->deleteAllByUserId($userId);
    }
}
