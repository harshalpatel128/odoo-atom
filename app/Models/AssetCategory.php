<?php

namespace App\Models;

use App\Core\Database;

final class AssetCategory
{
    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM asset_categories ORDER BY name')->fetchAll();
    }

    public static function create(array $data): void
    {
        $stmt = Database::pdo()->prepare('INSERT INTO asset_categories (name, description, custom_fields_json, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['description'] ?? null, self::jsonOrNull($data['custom_fields_json'] ?? null), $data['status'] ?? 'active']);
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::pdo()->prepare('UPDATE asset_categories SET name = ?, description = ?, custom_fields_json = ?, status = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['description'] ?? null, self::jsonOrNull($data['custom_fields_json'] ?? null), $data['status'] ?? 'active', $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM asset_categories WHERE id = ?')->execute([$id]);
    }

    private static function jsonOrNull(?string $json): ?string
    {
        if (!$json) {
            return null;
        }
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE ? $json : null;
    }
}
