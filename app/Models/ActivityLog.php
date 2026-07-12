<?php

namespace App\Models;

use App\Core\Database;
use PDOException;

final class ActivityLog
{
    public static function write(?int $userId, string $action, string $module): void
    {
        try {
            $stmt = Database::pdo()->prepare(
                'INSERT INTO activity_logs (user_id, role_name, action, module, ip_address, user_agent, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $_SESSION['user']['role_name'] ?? null,
                $action,
                $module,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI', 0, 255),
                'Success',
            ]);
        } catch (PDOException) {
        }
    }

    public static function recent(int $limit = 12): array
    {
        try {
            $stmt = Database::pdo()->prepare(
                'SELECT activity_logs.*, users.name FROM activity_logs
                 LEFT JOIN users ON users.id = activity_logs.user_id
                 ORDER BY activity_logs.created_at DESC LIMIT ?'
            );
            $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException) {
            return [];
        }
    }
}
