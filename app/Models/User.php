<?php

namespace App\Models;

use App\Core\Database;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT users.*, roles.name AS role_name, roles.slug AS role_slug, departments.name AS department_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             LEFT JOIN departments ON departments.id = users.department_id
             WHERE users.email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT users.*, roles.name AS role_name, roles.slug AS role_slug, departments.name AS department_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             LEFT JOIN departments ON departments.id = users.department_id
             WHERE users.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function createEmployee(string $name, string $email, string $password): int
    {
        $roleId = Database::pdo()->query("SELECT id FROM roles WHERE slug = 'employee' LIMIT 1")->fetchColumn();
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, "active")'
        );
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $roleId]);
        $id = (int) $pdo->lastInsertId();
        $employeeCode = 'EMP' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
        $pdo->prepare('INSERT INTO employees (user_id, employee_code, designation, joining_date) VALUES (?, ?, "Employee", CURDATE())')
            ->execute([$id, $employeeCode]);
        return $id;
    }

    public static function allWithRoles(): array
    {
        return Database::pdo()->query(
            'SELECT users.*, roles.name AS role_name, departments.name AS department_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             LEFT JOIN departments ON departments.id = users.department_id
             ORDER BY users.created_at DESC'
        )->fetchAll();
    }

    public static function promote(int $userId, string $roleSlug): void
    {
        $stmt = Database::pdo()->prepare('UPDATE users SET role_id = (SELECT id FROM roles WHERE slug = ?) WHERE id = ?');
        $stmt->execute([$roleSlug, $userId]);
    }

    public static function create(array $data): int
    {
        $role = $data['role'] ?? 'employee';
        if (!in_array($role, ['employee', 'department_head', 'asset_manager'], true)) {
            $role = 'employee';
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role_id, department_id, phone, status) VALUES (?, ?, ?, (SELECT id FROM roles WHERE slug = ?), NULLIF(?, 0), ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'] ?? 'Employee@123', PASSWORD_DEFAULT),
            $role,
            (int) ($data['department_id'] ?? 0),
            $data['phone'] ?? null,
            $data['status'] ?? 'active',
        ]);
        $id = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO employees (user_id, employee_code, designation, joining_date) VALUES (?, ?, ?, NULLIF(?, ""))')
            ->execute([$id, $data['employee_code'] ?? ('EMP' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)), $data['designation'] ?? 'Employee', $data['joining_date'] ?? '']);
        $pdo->commit();
        return $id;
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            return;
        }
        Database::pdo()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public static function updateProfile(int $id, array $data): void
    {
        Database::pdo()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?')
            ->execute([trim((string) $data['name']), trim((string) ($data['phone'] ?? '')), $id]);
    }

    public static function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $user = self::find($id);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return false;
        }
        Database::pdo()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
        return true;
    }

    public static function setRememberToken(int $id, ?string $token): void
    {
        $hash = $token ? hash('sha256', $token) : null;
        Database::pdo()->prepare('UPDATE users SET remember_token_hash = ? WHERE id = ?')->execute([$hash, $id]);
    }

    public static function findByRememberToken(string $token): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT users.*, roles.name AS role_name, roles.slug AS role_slug
             FROM users JOIN roles ON roles.id = users.role_id
             WHERE users.remember_token_hash = ? AND users.status = "active" LIMIT 1'
        );
        $stmt->execute([hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }

    public static function createPasswordReset(int $userId, string $token): void
    {
        $stmt = Database::pdo()->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
        $stmt->execute([$userId, hash('sha256', $token)]);
    }

    public static function resetPassword(string $token, string $password): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        $stmt->execute([hash('sha256', $token)]);
        $reset = $stmt->fetch();
        if (!$reset) {
            return false;
        }
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), (int) $reset['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([(int) $reset['id']]);
        $pdo->commit();
        return true;
    }
}
