<?php

namespace App\Models;

use App\Core\Database;

final class Report
{
    public static function datasets(array $filters = []): array
    {
        $pdo = Database::pdo();
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $departmentId = (int) ($filters['department_id'] ?? 0);
        $categoryId = (int) ($filters['category_id'] ?? 0);

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(a.asset_tag LIKE ? OR a.name LIKE ? OR a.serial_number LIKE ? OR a.vendor LIKE ?)';
            array_push($params, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%");
        }
        if ($status !== '') {
            $where[] = 'a.lifecycle_status = ?';
            $params[] = $status;
        }
        if ($departmentId > 0) {
            $where[] = 'a.department_id = ?';
            $params[] = $departmentId;
        }
        if ($categoryId > 0) {
            $where[] = 'a.category_id = ?';
            $params[] = $categoryId;
        }
        $assetWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $assetStmt = $pdo->prepare(
            "SELECT a.asset_tag, a.name, c.name AS category, COALESCE(d.name, 'Unassigned') AS department,
                    COALESCE(u.name, 'Unassigned') AS assigned_to, a.lifecycle_status, a.purchase_cost, a.vendor
             FROM assets a
             JOIN asset_categories c ON c.id = a.category_id
             LEFT JOIN departments d ON d.id = a.department_id
             LEFT JOIN users u ON u.id = a.assigned_user_id
             {$assetWhere}
             ORDER BY a.created_at DESC
             LIMIT 250"
        );
        $assetStmt->execute($params);

        return [
            'assets' => $assetStmt->fetchAll(),
            'allocations' => $pdo->query('SELECT aa.created_at, a.asset_tag, a.name AS asset_name, u.name AS employee, aa.status, aa.expected_return_date FROM asset_allocations aa JOIN assets a ON a.id = aa.asset_id JOIN users u ON u.id = aa.employee_id ORDER BY aa.created_at DESC LIMIT 100')->fetchAll(),
            'transfers' => $pdo->query('SELECT t.created_at, a.asset_tag, a.name AS asset_name, from_user.name AS from_name, to_user.name AS to_name, t.status FROM asset_transfers t JOIN assets a ON a.id = t.asset_id LEFT JOIN users from_user ON from_user.id = t.from_user_id JOIN users to_user ON to_user.id = t.to_user_id ORDER BY t.created_at DESC LIMIT 100')->fetchAll(),
            'bookings' => $pdo->query('SELECT rb.starts_at, rb.ends_at, r.name AS resource, u.name AS user_name, rb.status FROM resource_bookings rb JOIN resources r ON r.id = rb.resource_id JOIN users u ON u.id = rb.user_id ORDER BY rb.starts_at DESC LIMIT 100')->fetchAll(),
            'maintenance' => $pdo->query('SELECT mr.created_at, a.asset_tag, a.name AS asset_name, requester.name AS requester, mr.priority, mr.status, mr.repair_cost FROM maintenance_requests mr JOIN assets a ON a.id = mr.asset_id JOIN users requester ON requester.id = mr.requested_by ORDER BY mr.created_at DESC LIMIT 100')->fetchAll(),
            'audits' => $pdo->query('SELECT ac.name, ac.scope, COALESCE(d.name, "All Departments") AS department, ac.start_date, ac.end_date, ac.status FROM audit_cycles ac LEFT JOIN departments d ON d.id = ac.department_id ORDER BY ac.created_at DESC LIMIT 100')->fetchAll(),
            'departments' => $pdo->query('SELECT d.name, d.status, COUNT(u.id) AS employees, COUNT(a.id) AS assets FROM departments d LEFT JOIN users u ON u.department_id = d.id LEFT JOIN assets a ON a.department_id = d.id GROUP BY d.id ORDER BY d.name LIMIT 100')->fetchAll(),
            'employees' => $pdo->query('SELECT u.name, u.email, r.name AS role, COALESCE(d.name, "Unassigned") AS department, u.status, COUNT(a.id) AS allocated_assets FROM users u JOIN roles r ON r.id = u.role_id LEFT JOIN departments d ON d.id = u.department_id LEFT JOIN assets a ON a.assigned_user_id = u.id GROUP BY u.id ORDER BY u.name LIMIT 250')->fetchAll(),
            'summary' => [
                'departments' => $pdo->query('SELECT d.name, COUNT(a.id) AS assets FROM departments d LEFT JOIN assets a ON a.department_id = d.id GROUP BY d.id ORDER BY assets DESC')->fetchAll(),
                'categories' => $pdo->query('SELECT c.name, COUNT(a.id) AS assets FROM asset_categories c LEFT JOIN assets a ON a.category_id = c.id GROUP BY c.id ORDER BY assets DESC')->fetchAll(),
                'vendors' => $pdo->query('SELECT COALESCE(vendor, "Unspecified") AS name, COUNT(*) AS assets, SUM(COALESCE(purchase_cost, 0)) AS value FROM assets GROUP BY vendor ORDER BY assets DESC LIMIT 25')->fetchAll(),
            ],
        ];
    }
}
