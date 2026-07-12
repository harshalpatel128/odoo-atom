<?php

namespace App\Models;

use App\Core\Database;

final class Department
{
    public static function all(): array
    {
        return Database::pdo()->query(
            'SELECT departments.*, users.name AS head_name, parent.name AS parent_name
             FROM departments
             LEFT JOIN users ON users.id = departments.head_user_id
             LEFT JOIN departments parent ON parent.id = departments.parent_id
             ORDER BY departments.name'
        )->fetchAll();
    }

    public static function create(array $data): void
    {
        $stmt = Database::pdo()->prepare('INSERT INTO departments (name, head_user_id, parent_id, status) VALUES (?, NULLIF(?, 0), NULLIF(?, 0), ?)');
        $stmt->execute([$data['name'], (int) ($data['head_user_id'] ?? 0), (int) ($data['parent_id'] ?? 0), $data['status'] ?? 'active']);
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare('UPDATE departments SET name = ?, head_user_id = NULLIF(?, 0), parent_id = NULLIF(?, 0), status = ? WHERE id = ?');
        $stmt->execute([$data['name'], (int) ($data['head_user_id'] ?? 0), (int) ($data['parent_id'] ?? 0), $data['status'] ?? 'active', $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM departments WHERE id = ?')->execute([$id]);
    }
}
