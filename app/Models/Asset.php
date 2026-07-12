<?php

namespace App\Models;

use App\Core\Database;

final class Asset
{
    public static function all(int $limit = 200): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT assets.*, asset_categories.name AS category_name, departments.name AS department_name, users.name AS assigned_name
             FROM assets
             JOIN asset_categories ON asset_categories.id = assets.category_id
             LEFT JOIN departments ON departments.id = assets.department_id
             LEFT JOIN users ON users.id = assets.assigned_user_id
             ORDER BY assets.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function nextTag(): string
    {
        $last = (int) Database::pdo()->query("SELECT COALESCE(MAX(CAST(SUBSTRING(asset_tag, 4) AS UNSIGNED)), 0) FROM assets WHERE asset_tag LIKE 'AF-%'")->fetchColumn();
        return 'AF-' . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
    }

    public static function create(array $data): int
    {
        $tag = trim($data['asset_tag'] ?? '') ?: self::nextTag();
        $stmt = Database::pdo()->prepare(
            'INSERT INTO assets (asset_tag, name, category_id, serial_number, department_id, assigned_user_id, location, asset_condition, purchase_date, purchase_cost, warranty_expiry, vendor, qr_code, barcode, photo_path, is_shared_bookable, lifecycle_status)
             VALUES (?, ?, ?, NULLIF(?, ""), NULLIF(?, 0), NULLIF(?, 0), ?, ?, NULLIF(?, ""), NULLIF(?, ""), NULLIF(?, ""), ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $tag,
            $data['name'],
            (int) $data['category_id'],
            $data['serial_number'] ?? '',
            (int) ($data['department_id'] ?? 0),
            (int) ($data['assigned_user_id'] ?? 0),
            $data['location'] ?? null,
            $data['asset_condition'] ?? 'Good',
            $data['purchase_date'] ?? '',
            $data['purchase_cost'] ?? '',
            $data['warranty_expiry'] ?? '',
            $data['vendor'] ?? null,
            'QR-' . $tag,
            'BC-' . $tag,
            $data['photo_path'] ?? null,
            !empty($data['is_shared_bookable']) ? 1 : 0,
            $data['lifecycle_status'] ?? 'Available',
        ]);
        $id = (int) Database::pdo()->lastInsertId();
        self::history($id, $_SESSION['user']['id'] ?? null, 'Created asset', $tag);
        return $id;
    }

    public static function history(int $assetId, ?int $userId, string $action, ?string $notes = null): void
    {
        $stmt = Database::pdo()->prepare('INSERT INTO asset_history (asset_id, user_id, action, notes) VALUES (?, ?, ?, ?)');
        $stmt->execute([$assetId, $userId, $action, $notes]);
    }
}
