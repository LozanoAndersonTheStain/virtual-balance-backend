<?php

namespace VirtualBalance\Application\UseCases\Notification;

use VirtualBalance\Domain\Repositories\NotificationRepositoryInterface;

class GetNotificationUseCase
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function execute(int $userId): array
    {
        return $this->notificationRepository->findByUserId($userId);
    }
}

