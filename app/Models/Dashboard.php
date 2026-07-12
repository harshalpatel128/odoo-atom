<?php

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Dashboard
{
    public static function counts(): array
    {
        $defaults = [
            'total_assets' => 0,
            'available' => 0,
            'allocated' => 0,
            'maintenance_assets' => 0,
            'retired_assets' => 0,
            'lost_assets' => 0,
            'todays_bookings' => 0,
            'pending_bookings' => 0,
            'pending_transfers' => 0,
            'pending_maintenance' => 0,
            'upcoming_returns' => 0,
            'pending_audits' => 0,
        ];

        try {
            $pdo = Database::pdo();
            return [
                'total_assets' => (int) $pdo->query('SELECT COUNT(*) FROM assets')->fetchColumn(),
                'available' => (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE lifecycle_status = 'Available'")->fetchColumn(),
                'allocated' => (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE lifecycle_status = 'Allocated'")->fetchColumn(),
                'maintenance_assets' => (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE lifecycle_status IN ('Maintenance','Under Maintenance')")->fetchColumn(),
                'retired_assets' => (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE lifecycle_status IN ('Retired','Disposed')")->fetchColumn(),
                'lost_assets' => (int) $pdo->query("SELECT COUNT(*) FROM assets WHERE lifecycle_status = 'Lost'")->fetchColumn(),
                'todays_bookings' => (int) $pdo->query('SELECT COUNT(*) FROM resource_bookings WHERE DATE(starts_at) = CURDATE()')->fetchColumn(),
                'pending_bookings' => (int) $pdo->query("SELECT COUNT(*) FROM resource_bookings WHERE status = 'Pending'")->fetchColumn(),
                'pending_transfers' => (int) $pdo->query("SELECT COUNT(*) FROM asset_transfers WHERE status = 'Requested'")->fetchColumn(),
                'pending_maintenance' => (int) $pdo->query("SELECT COUNT(*) FROM maintenance_requests WHERE status IN ('Pending','Approved','Technician Assigned','In Progress')")->fetchColumn(),
                'upcoming_returns' => (int) $pdo->query("SELECT COUNT(*) FROM asset_allocations WHERE expected_return_date >= CURDATE() AND returned_at IS NULL")->fetchColumn(),
                'pending_audits' => (int) $pdo->query("SELECT COUNT(*) FROM audit_cycles WHERE status IN ('Open','Locked')")->fetchColumn(),
            ];
        } catch (PDOException) {
            return $defaults;
        }
    }

    public static function charts(): array
    {
        return [
            'assetStatus' => self::pairs('SELECT lifecycle_status AS label, COUNT(*) AS value FROM assets GROUP BY lifecycle_status'),
            'departmentAllocation' => self::pairs('SELECT COALESCE(d.name, "Unassigned") AS label, COUNT(a.id) AS value FROM assets a LEFT JOIN departments d ON d.id = a.department_id GROUP BY label ORDER BY value DESC LIMIT 10'),
            'assetCategory' => self::pairs('SELECT c.name AS label, COUNT(a.id) AS value FROM assets a JOIN asset_categories c ON c.id = a.category_id GROUP BY c.id ORDER BY value DESC LIMIT 10'),
            'monthlyBookings' => self::pairs('SELECT DATE_FORMAT(starts_at, "%Y-%m") AS label, COUNT(*) AS value FROM resource_bookings GROUP BY label ORDER BY label DESC LIMIT 12'),
            'monthlyMaintenance' => self::pairs('SELECT DATE_FORMAT(created_at, "%Y-%m") AS label, COUNT(*) AS value FROM maintenance_requests GROUP BY label ORDER BY label DESC LIMIT 12'),
            'auditSummary' => self::pairs('SELECT status AS label, COUNT(*) AS value FROM audit_cycles GROUP BY status'),
        ];
    }

    public static function latest(): array
    {
        $pdo = Database::pdo();
        return [
            'bookings' => $pdo->query('SELECT rb.*, r.name AS resource_name, u.name AS user_name FROM resource_bookings rb JOIN resources r ON r.id = rb.resource_id JOIN users u ON u.id = rb.user_id ORDER BY rb.created_at DESC LIMIT 5')->fetchAll(),
            'transfers' => $pdo->query('SELECT t.*, a.asset_tag, a.name AS asset_name FROM asset_transfers t JOIN assets a ON a.id = t.asset_id ORDER BY t.created_at DESC LIMIT 5')->fetchAll(),
            'maintenance' => $pdo->query('SELECT mr.*, a.asset_tag, a.name AS asset_name FROM maintenance_requests mr JOIN assets a ON a.id = mr.asset_id ORDER BY mr.created_at DESC LIMIT 5')->fetchAll(),
            'notifications' => $pdo->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5')->fetchAll(),
        ];
    }

    private static function pairs(string $sql): array
    {
        try {
            return Database::pdo()->query($sql)->fetchAll();
        } catch (PDOException) {
            return [];
        }
    }
}
