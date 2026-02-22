<?php

namespace VirtualBalance\Domain\Repositories;

use VirtualBalance\Domain\Entities\Notification;

interface NotificationRepositoryInterface
{
    /**
     * @param int $userId
     * @return Notification[]
     */
    public function findByUserId(int $userId): array;
    public function save(Notification $notification): void;
    public function deleteAllByUserId(int $userId): void;
    public function markAsReadById(int $notificationId): void;
    public function markAsUnreadById(int $notificationId): void;
}
