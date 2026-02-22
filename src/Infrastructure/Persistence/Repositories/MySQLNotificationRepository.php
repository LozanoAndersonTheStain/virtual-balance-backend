<?php

namespace VirtualBalance\Infrastructure\Persistence\Repositories;

use PDO;
use VirtualBalance\Domain\Entities\Notification;
use VirtualBalance\Domain\Repositories\NotificationRepositoryInterface;
use VirtualBalance\Infrastructure\Persistence\Database\Connection;

class MySQLNotificationRepository implements NotificationRepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    /**
     * @param int $userId
     * @return Notification[]
     */
    public function findByUserId(int $userId): array
    {
        $sql = 'SELECT id, user_id, title, message, is_read, created_at, updated_at FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $notifications = [];
        foreach ($rows as $row) {
            $notifications[] = new Notification(
                id: (int)$row['id'],
                userId: (int)$row['user_id'],
                title: $row['title'],
                message: $row['message'],
                isRead: (bool)$row['is_read'],
                createdAt: $row['created_at'],
                updatedAt: $row['updated_at'] ?? null
            );
        }
        return $notifications;
    }
    
    public function save(Notification $notification): void
    {
        $sql = 'INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:user_id, :title, :message, :is_read, :created_at)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $notification->userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $notification->title, PDO::PARAM_STR);
        $stmt->bindValue(':message', $notification->message, PDO::PARAM_STR);
        $stmt->bindValue(':is_read', $notification->isRead ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':created_at', $notification->createdAt, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function deleteAllByUserId(int $userId): void
    {
        $sql = 'DELETE FROM notifications WHERE user_id = :user_id';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function markAsReadById(int $notificationId): void
    {
        $sql = 'UPDATE notifications SET is_read = 1 WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function markAsUnreadById(int $notificationId): void
    {
        $sql = 'UPDATE notifications SET is_read = 0 WHERE id = :id';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
        $stmt->execute();
    }
}