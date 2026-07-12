<?php

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Notification
{
    public static function create(?int $userId, string $type, string $title, ?string $body = null): void
    {
        if (!$userId) {
            return;
        }

        try {
            Database::pdo()->prepare('INSERT INTO notifications (user_id, type, title, body) VALUES (?, ?, ?, ?)')
                ->execute([$userId, $type, $title, $body]);
        } catch (PDOException) {
        }
    }

    public static function forUser(int $userId, int $limit = 100): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function all(int $limit = 100): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT notifications.*, users.name AS user_name
             FROM notifications
             JOIN users ON users.id = notifications.user_id
             ORDER BY notifications.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markAllRead(int $userId): void
    {
        Database::pdo()->prepare('UPDATE notifications SET read_at = COALESCE(read_at, NOW()) WHERE user_id = ?')
            ->execute([$userId]);
    }

    public static function markRead(int $notificationId, int $userId, bool $admin = false): void
    {
        $sql = 'UPDATE notifications SET read_at = COALESCE(read_at, NOW()) WHERE id = ?';
        $params = [$notificationId];
        if (!$admin) {
            $sql .= ' AND user_id = ?';
            $params[] = $userId;
        }
        Database::pdo()->prepare($sql)->execute($params);
    }

    public static function delete(int $notificationId, int $userId, bool $admin = false): void
    {
        $sql = 'DELETE FROM notifications WHERE id = ?';
        $params = [$notificationId];
        if (!$admin) {
            $sql .= ' AND user_id = ?';
            $params[] = $userId;
        }
        Database::pdo()->prepare($sql)->execute($params);
    }
}
