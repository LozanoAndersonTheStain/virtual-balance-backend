<?php

namespace VirtualBalance\Application\UseCases\Notification;

use VirtualBalance\Domain\Repositories\NotificationRepositoryInterface;
use VirtualBalance\Domain\Entities\Notification;

class CreateNotificationUseCase
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function execute(int $userId, string $title, string $message): void
    {
        $notification = new Notification(
            id: 0,
            userId: $userId,
            title: $title,
            message: $message,
            isRead: false,
            createdAt: date('Y-m-d H:i:s')
        );
        $this->notificationRepository->save($notification);
    }
}

