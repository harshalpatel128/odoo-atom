<?php

namespace App\Models;

use App\Core\Database;
use RuntimeException;

final class Workflow
{
    private const BLOCKED_ALLOCATION_STATUSES = ['Allocated', 'Reserved', 'Maintenance', 'Under Maintenance', 'Lost', 'Retired', 'Disposed'];

    public static function allocate(array $data, int $adminId): void
    {
        $asset = self::asset((int) $data['asset_id']);
        if (!$asset) {
            throw new RuntimeException('Asset was not found.');
        }
        if (in_array($asset['lifecycle_status'], self::BLOCKED_ALLOCATION_STATUSES, true)) {
            throw new RuntimeException('This asset cannot be allocated while its status is ' . $asset['lifecycle_status'] . '.');
        }
        if (self::activeAllocation((int) $data['asset_id'])) {
            throw new RuntimeException('This asset already has an active allocation.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO asset_allocations
                 (asset_id, employee_id, department_id, allocated_by, expected_return_date, condition_before, status, notes)
                 VALUES (?, ?, NULLIF(?, 0), ?, NULLIF(?, ""), ?, "Allocated", ?)'
            );
            $stmt->execute([
                (int) $data['asset_id'],
                (int) $data['employee_id'],
                (int) ($data['department_id'] ?? 0),
                $adminId,
                $data['expected_return_date'] ?? '',
                $asset['asset_condition'],
                $data['notes'] ?? null,
            ]);
            $pdo->prepare("UPDATE assets SET assigned_user_id = ?, department_id = NULLIF(?, 0), lifecycle_status = 'Allocated' WHERE id = ?")
                ->execute([(int) $data['employee_id'], (int) ($data['department_id'] ?? 0), (int) $data['asset_id']]);
            Asset::history((int) $data['asset_id'], $adminId, 'Allocated asset', $data['notes'] ?? null);
            Notification::create((int) $data['employee_id'], 'asset_allocated', 'Asset allocated', $asset['asset_tag'] . ' has been allocated to you.');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function returnAllocation(array $data, int $adminId): void
    {
        $allocation = self::allocation((int) $data['allocation_id']);
        if (!$allocation || $allocation['status'] !== 'Allocated') {
            throw new RuntimeException('Only active allocations can be returned.');
        }

        $conditionAfter = $data['condition_after'] ?: $allocation['asset_condition'];
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE asset_allocations SET returned_at = NOW(), condition_after = ?, return_condition_notes = ?, status = "Returned" WHERE id = ?')
                ->execute([$conditionAfter, $data['return_condition_notes'] ?? null, (int) $data['allocation_id']]);
            $pdo->prepare("UPDATE assets SET assigned_user_id = NULL, asset_condition = ?, lifecycle_status = 'Available' WHERE id = ?")
                ->execute([$conditionAfter, (int) $allocation['asset_id']]);
            Asset::history((int) $allocation['asset_id'], $adminId, 'Returned asset', $data['return_condition_notes'] ?? null);
            Notification::create((int) $allocation['employee_id'], 'asset_returned', 'Asset return completed', $allocation['asset_tag'] . ' has been marked returned.');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function cancelAllocation(int $allocationId, int $adminId): void
    {
        $allocation = self::allocation($allocationId);
        if (!$allocation || $allocation['status'] !== 'Allocated') {
            throw new RuntimeException('Only active allocations can be cancelled.');
        }
        Database::pdo()->prepare('UPDATE asset_allocations SET status = "Cancelled" WHERE id = ?')->execute([$allocationId]);
        Database::pdo()->prepare("UPDATE assets SET assigned_user_id = NULL, lifecycle_status = 'Available' WHERE id = ?")->execute([(int) $allocation['asset_id']]);
        Asset::history((int) $allocation['asset_id'], $adminId, 'Cancelled allocation');
    }

    public static function allocations(int $limit = 200): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT aa.*, a.asset_tag, a.name AS asset_name, a.asset_condition, u.name AS employee_name,
                    d.name AS department_name, approver.name AS allocated_by_name
             FROM asset_allocations aa
             JOIN assets a ON a.id = aa.asset_id
             JOIN users u ON u.id = aa.employee_id
             LEFT JOIN departments d ON d.id = aa.department_id
             LEFT JOIN users approver ON approver.id = aa.allocated_by
             ORDER BY aa.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function requestTransfer(array $data, int $userId): void
    {
        $asset = self::asset((int) $data['asset_id']);
        if (!$asset) {
            throw new RuntimeException('Asset was not found.');
        }
        if (in_array($asset['lifecycle_status'], ['Lost', 'Retired', 'Disposed'], true)) {
            throw new RuntimeException('Lost, retired, and disposed assets cannot be transferred.');
        }
        if ((int) $asset['assigned_user_id'] !== $userId) {
            throw new RuntimeException('Only the current asset holder can request its transfer.');
        }
        if ((int) $data['to_user_id'] === $userId) {
            throw new RuntimeException('An asset cannot be transferred to its current holder.');
        }
        $duplicate = Database::pdo()->prepare('SELECT COUNT(*) FROM asset_transfers WHERE asset_id = ? AND status = "Requested"');
        $duplicate->execute([(int) $data['asset_id']]);
        if ((int) $duplicate->fetchColumn() > 0) {
            throw new RuntimeException('A transfer request is already pending for this asset.');
        }
        $stmt = Database::pdo()->prepare('INSERT INTO asset_transfers (asset_id, from_user_id, to_user_id, requested_by, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([(int) $data['asset_id'], $asset['assigned_user_id'] ?: $userId, (int) $data['to_user_id'], $userId, $data['notes'] ?? null]);
        Notification::create((int) $data['to_user_id'], 'transfer_requested', 'Transfer requested', $asset['asset_tag'] . ' has been requested for transfer.');
    }

    public static function decideTransfer(int $transferId, string $status, int $adminId): void
    {
        $transfer = self::transfer($transferId);
        if (!$transfer || $transfer['status'] !== 'Requested') {
            throw new RuntimeException('Only pending transfers can be decided.');
        }
        if (!in_array($status, ['Approved', 'Rejected'], true)) {
            throw new RuntimeException('Invalid transfer decision.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE asset_transfers SET status = ?, approved_by = ?, decided_at = NOW() WHERE id = ?')
                ->execute([$status, $adminId, $transferId]);
            if ($status === 'Approved') {
                $pdo->prepare("UPDATE assets SET assigned_user_id = ?, department_id = (SELECT department_id FROM users WHERE id = ?), lifecycle_status = 'Allocated' WHERE id = ?")
                    ->execute([(int) $transfer['to_user_id'], (int) $transfer['to_user_id'], (int) $transfer['asset_id']]);
                Asset::history((int) $transfer['asset_id'], $adminId, 'Approved transfer', $transfer['notes']);
            }
            Notification::create((int) $transfer['requested_by'], 'transfer_' . strtolower($status), 'Transfer ' . strtolower($status), $transfer['asset_tag'] . ' transfer was ' . strtolower($status) . '.');
            Notification::create((int) $transfer['to_user_id'], 'transfer_' . strtolower($status), 'Transfer ' . strtolower($status), $transfer['asset_tag'] . ' transfer was ' . strtolower($status) . '.');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function transfers(?int $userId = null, int $limit = 200): array
    {
        $sql = 'SELECT at.*, a.asset_tag, a.name AS asset_name, from_user.name AS from_name, to_user.name AS to_name,
                       requester.name AS requester_name, approver.name AS approver_name
                FROM asset_transfers at
                JOIN assets a ON a.id = at.asset_id
                LEFT JOIN users from_user ON from_user.id = at.from_user_id
                JOIN users to_user ON to_user.id = at.to_user_id
                JOIN users requester ON requester.id = at.requested_by
                LEFT JOIN users approver ON approver.id = at.approved_by';
        $params = [];
        if ($userId) {
            $sql .= ' WHERE at.requested_by = ? OR at.from_user_id = ? OR at.to_user_id = ?';
            $params = [$userId, $userId, $userId];
        }
        $sql .= ' ORDER BY at.created_at DESC LIMIT ?';
        $stmt = Database::pdo()->prepare($sql);
        foreach ($params as $index => $param) {
            $stmt->bindValue($index + 1, $param, \PDO::PARAM_INT);
        }
        $stmt->bindValue(count($params) + 1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function bookResource(array $data, int $userId): bool
    {
        if (strtotime($data['starts_at']) >= strtotime($data['ends_at'])) {
            throw new RuntimeException('Booking end time must be after start time.');
        }
        if (self::bookingOverlap((int) $data['resource_id'], $data['starts_at'], $data['ends_at'])) {
            return false;
        }
        Database::pdo()->prepare('INSERT INTO resource_bookings (resource_id, user_id, purpose, starts_at, ends_at, status) VALUES (?, ?, ?, ?, ?, "Pending")')
            ->execute([(int) $data['resource_id'], $userId, $data['purpose'] ?? null, $data['starts_at'], $data['ends_at']]);
        Notification::create($userId, 'booking_requested', 'Booking submitted', 'Your booking request is pending approval.');
        return true;
    }

    public static function decideBooking(int $bookingId, string $status, int $adminId): void
    {
        $booking = self::booking($bookingId);
        if (!$booking || !in_array($booking['status'], ['Pending', 'Approved'], true)) {
            throw new RuntimeException('This booking cannot be updated.');
        }
        if (!in_array($status, ['Approved', 'Rejected', 'Cancelled', 'Completed'], true)) {
            throw new RuntimeException('Invalid booking status.');
        }
        if ($status === 'Approved' && self::bookingOverlap((int) $booking['resource_id'], $booking['starts_at'], $booking['ends_at'], $bookingId)) {
            throw new RuntimeException('This resource is already approved for that time window.');
        }
        Database::pdo()->prepare('UPDATE resource_bookings SET status = ? WHERE id = ?')->execute([$status, $bookingId]);
        Notification::create((int) $booking['user_id'], 'booking_' . strtolower($status), 'Booking ' . strtolower($status), $booking['resource_name'] . ' booking was ' . strtolower($status) . '.');
        ActivityLog::write($adminId, 'Updated booking to ' . $status, 'Bookings');
    }

    public static function bookings(?int $userId = null, int $limit = 200): array
    {
        self::refreshBookingStates();
        $sql = 'SELECT rb.*, r.name AS resource_name, r.resource_type, r.location, users.name AS user_name
                FROM resource_bookings rb
                JOIN resources r ON r.id = rb.resource_id
                JOIN users ON users.id = rb.user_id';
        $params = [];
        if ($userId) {
            $sql .= ' WHERE rb.user_id = ?';
            $params[] = $userId;
        }
        $sql .= ' ORDER BY rb.starts_at DESC LIMIT ?';
        $stmt = Database::pdo()->prepare($sql);
        foreach ($params as $index => $param) {
            $stmt->bindValue($index + 1, $param, \PDO::PARAM_INT);
        }
        $stmt->bindValue(count($params) + 1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function maintenance(array $data, int $userId, ?string $photoPath = null): void
    {
        $asset = self::asset((int) $data['asset_id']);
        if (!$asset || in_array($asset['lifecycle_status'], ['Lost', 'Retired', 'Disposed'], true)) {
            throw new RuntimeException('This asset cannot enter maintenance.');
        }
        if ((int) $asset['assigned_user_id'] !== $userId) {
            throw new RuntimeException('You can request maintenance only for an asset allocated to you.');
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO maintenance_requests (asset_id, requested_by, description, priority, photo_path) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([(int) $data['asset_id'], $userId, $data['description'], $data['priority'] ?? 'Medium', $photoPath]);
            $requestId = (int) $pdo->lastInsertId();
            $pdo->prepare('INSERT INTO maintenance_history (maintenance_request_id, user_id, status, notes) VALUES (?, ?, "Pending", ?)')
                ->execute([$requestId, $userId, $data['description']]);
            Asset::history((int) $data['asset_id'], $userId, 'Requested maintenance', $data['description']);
            Notification::create($userId, 'maintenance_requested', 'Maintenance submitted', $asset['asset_tag'] . ' maintenance request was submitted.');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateMaintenance(array $data, int $userId): void
    {
        $request = self::maintenanceRequest((int) $data['maintenance_request_id']);
        if (!$request) {
            throw new RuntimeException('Maintenance request was not found.');
        }
        $status = $data['status'];
        if (!in_array($status, ['Approved', 'Rejected', 'Technician Assigned', 'In Progress', 'Completed', 'Resolved'], true)) {
            throw new RuntimeException('Invalid maintenance status.');
        }

        $assetStatus = in_array($status, ['Approved', 'Technician Assigned', 'In Progress'], true) ? 'Under Maintenance' : null;
        $complete = in_array($status, ['Completed', 'Resolved', 'Rejected'], true);
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE maintenance_requests SET status = ?, technician_user_id = NULLIF(?, 0), repair_cost = NULLIF(?, ""), repair_notes = ?, resolved_at = IF(? IN ("Completed", "Resolved"), NOW(), resolved_at) WHERE id = ?');
            $stmt->execute([$status, (int) ($data['technician_user_id'] ?? 0), $data['repair_cost'] ?? '', $data['notes'] ?? null, $status, (int) $data['maintenance_request_id']]);
            $pdo->prepare('INSERT INTO maintenance_history (maintenance_request_id, user_id, status, notes, repair_cost) VALUES (?, ?, ?, ?, NULLIF(?, ""))')
                ->execute([(int) $data['maintenance_request_id'], $userId, $status, $data['notes'] ?? null, $data['repair_cost'] ?? '']);
            if ($assetStatus) {
                $pdo->prepare('UPDATE assets SET lifecycle_status = ? WHERE id = ?')->execute([$assetStatus, (int) $request['asset_id']]);
            } elseif ($complete) {
                $pdo->prepare("UPDATE assets SET lifecycle_status = IF(assigned_user_id IS NULL, 'Available', 'Allocated') WHERE id = ?")->execute([(int) $request['asset_id']]);
            }
            Notification::create((int) $request['requested_by'], 'maintenance_' . strtolower(str_replace(' ', '_', $status)), 'Maintenance ' . strtolower($status), $request['asset_tag'] . ' maintenance is now ' . strtolower($status) . '.');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function maintenanceRequests(?int $userId = null, int $limit = 200): array
    {
        $sql = 'SELECT mr.*, a.asset_tag, a.name AS asset_name, requester.name AS requester_name, tech.name AS technician_name
                FROM maintenance_requests mr
                JOIN assets a ON a.id = mr.asset_id
                JOIN users requester ON requester.id = mr.requested_by
                LEFT JOIN users tech ON tech.id = mr.technician_user_id';
        $params = [];
        if ($userId) {
            $sql .= ' WHERE mr.requested_by = ?';
            $params[] = $userId;
        }
        $sql .= ' ORDER BY mr.created_at DESC LIMIT ?';
        $stmt = Database::pdo()->prepare($sql);
        foreach ($params as $index => $param) {
            $stmt->bindValue($index + 1, $param, \PDO::PARAM_INT);
        }
        $stmt->bindValue(count($params) + 1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function createAudit(array $data, int $adminId): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO audit_cycles (name, scope, department_id, location, start_date, end_date) VALUES (?, ?, NULLIF(?, 0), ?, ?, ?)');
            $stmt->execute([$data['name'], $data['scope'], (int) ($data['department_id'] ?? 0), $data['location'] ?? null, $data['start_date'], $data['end_date']]);
            $cycleId = (int) $pdo->lastInsertId();
            $assets = self::auditScopeAssets((int) ($data['department_id'] ?? 0), $data['location'] ?? null);
            $insert = $pdo->prepare('INSERT INTO audit_assets (audit_cycle_id, asset_id, auditor_user_id) VALUES (?, ?, NULLIF(?, 0))');
            foreach ($assets as $asset) {
                $insert->execute([$cycleId, (int) $asset['id'], (int) ($data['auditor_user_id'] ?? 0)]);
            }
            if (!empty($data['auditor_user_id'])) {
                Notification::create((int) $data['auditor_user_id'], 'audit_assigned', 'Audit assigned', $data['name'] . ' has been assigned to you.');
            }
            ActivityLog::write($adminId, 'Created audit cycle', 'Audits');
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateAuditAsset(array $data, int $userId): void
    {
        $auditAssetId = (int) $data['audit_asset_id'];
        $result = $data['result'];
        if (!in_array($result, ['Verified', 'Missing', 'Damaged'], true)) {
            throw new RuntimeException('Invalid audit result.');
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $item = self::auditAsset($auditAssetId);
            if (!$item || !self::auditItemIsOpen($auditAssetId)) {
                throw new RuntimeException('This audit item is no longer available for verification.');
            }
            if (!empty($item['auditor_user_id']) && (int) $item['auditor_user_id'] !== $userId && !self::isPrivilegedAuditor($userId)) {
                throw new RuntimeException('This audit item is assigned to another auditor.');
            }
            $pdo->prepare('UPDATE audit_assets SET result = ?, notes = ? WHERE id = ?')
                ->execute([$result, $data['notes'] ?? null, $auditAssetId]);
            if ($result !== 'Verified') {
                $pdo->prepare('INSERT INTO audit_discrepancies (audit_asset_id, discrepancy_type, notes) VALUES (?, ?, ?)')
                    ->execute([$auditAssetId, $result, $data['notes'] ?? null]);
                $pdo->prepare('UPDATE assets SET lifecycle_status = ? WHERE id = ?')
                    ->execute([$result === 'Missing' ? 'Lost' : 'Under Maintenance', (int) $item['asset_id']]);
            }
            Asset::history((int) $item['asset_id'], $userId, 'Audit result: ' . $result, $data['notes'] ?? null);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function closeAudit(int $cycleId, int $adminId): void
    {
        Database::pdo()->prepare("UPDATE audit_cycles SET status = 'Closed' WHERE id = ?")->execute([$cycleId]);
        ActivityLog::write($adminId, 'Closed audit cycle', 'Audits');
    }

    public static function audits(int $limit = 100): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT ac.*, d.name AS department_name,
                    COUNT(aa.id) AS asset_count,
                    SUM(aa.result = "Missing") AS missing_count,
                    SUM(aa.result = "Damaged") AS damaged_count
             FROM audit_cycles ac
             LEFT JOIN departments d ON d.id = ac.department_id
             LEFT JOIN audit_assets aa ON aa.audit_cycle_id = ac.id
             GROUP BY ac.id
             ORDER BY ac.created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function auditAssets(int $limit = 200): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT aa.*, ac.name AS cycle_name, a.asset_tag, a.name AS asset_name, u.name AS auditor_name
             FROM audit_assets aa
             JOIN audit_cycles ac ON ac.id = aa.audit_cycle_id
             JOIN assets a ON a.id = aa.asset_id
             LEFT JOIN users u ON u.id = aa.auditor_user_id
             ORDER BY ac.created_at DESC, aa.id DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function auditAssetsForAuditor(int $userId, int $limit = 200): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT aa.*, ac.name AS cycle_name, ac.status AS cycle_status, a.asset_tag, a.name AS asset_name
             FROM audit_assets aa
             JOIN audit_cycles ac ON ac.id = aa.audit_cycle_id
             JOIN assets a ON a.id = aa.asset_id
             WHERE aa.auditor_user_id = ?
             ORDER BY ac.end_date, a.asset_tag LIMIT ?'
        );
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function createBookingReminders(int $userId): void
    {
        self::refreshBookingStates();
        $stmt = Database::pdo()->prepare(
            'SELECT rb.id, rb.starts_at, r.name AS resource_name
             FROM resource_bookings rb JOIN resources r ON r.id = rb.resource_id
             WHERE rb.user_id = ? AND rb.status IN ("Approved", "Upcoming")
               AND rb.starts_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)'
        );
        $stmt->execute([$userId]);
        $exists = Database::pdo()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = "booking_reminder" AND body = ?');
        foreach ($stmt->fetchAll() as $booking) {
            $body = $booking['resource_name'] . ' starts at ' . $booking['starts_at'] . '.';
            $exists->execute([$userId, $body]);
            if ((int) $exists->fetchColumn() === 0) {
                Notification::create($userId, 'booking_reminder', 'Upcoming booking reminder', $body);
            }
        }
    }

    public static function createOverdueAllocationNotifications(int $userId): void
    {
        $stmt = Database::pdo()->prepare(
            'SELECT aa.id, a.asset_tag, aa.expected_return_date
             FROM asset_allocations aa JOIN assets a ON a.id = aa.asset_id
             WHERE aa.employee_id = ? AND aa.status = "Allocated" AND aa.returned_at IS NULL
               AND aa.expected_return_date IS NOT NULL AND aa.expected_return_date < CURDATE()'
        );
        $stmt->execute([$userId]);
        $exists = Database::pdo()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = "asset_overdue" AND body = ?');
        foreach ($stmt->fetchAll() as $allocation) {
            $body = $allocation['asset_tag'] . ' was due on ' . $allocation['expected_return_date'] . '.';
            $exists->execute([$userId, $body]);
            if ((int) $exists->fetchColumn() === 0) {
                Notification::create($userId, 'asset_overdue', 'Asset return overdue', $body);
            }
        }
    }

    public static function resources(): array
    {
        return Database::pdo()->query("SELECT * FROM resources WHERE status = 'active' ORDER BY name")->fetchAll();
    }

    private static function asset(int $assetId): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM assets WHERE id = ?');
        $stmt->execute([$assetId]);
        return $stmt->fetch() ?: null;
    }

    private static function activeAllocation(int $assetId): bool
    {
        $stmt = Database::pdo()->prepare("SELECT COUNT(*) FROM asset_allocations WHERE asset_id = ? AND status = 'Allocated' AND returned_at IS NULL");
        $stmt->execute([$assetId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function allocation(int $allocationId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT aa.*, a.asset_tag, a.asset_condition
             FROM asset_allocations aa
             JOIN assets a ON a.id = aa.asset_id
             WHERE aa.id = ?'
        );
        $stmt->execute([$allocationId]);
        return $stmt->fetch() ?: null;
    }

    private static function transfer(int $transferId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT at.*, a.asset_tag
             FROM asset_transfers at
             JOIN assets a ON a.id = at.asset_id
             WHERE at.id = ?'
        );
        $stmt->execute([$transferId]);
        return $stmt->fetch() ?: null;
    }

    private static function booking(int $bookingId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT rb.*, r.name AS resource_name
             FROM resource_bookings rb
             JOIN resources r ON r.id = rb.resource_id
             WHERE rb.id = ?'
        );
        $stmt->execute([$bookingId]);
        return $stmt->fetch() ?: null;
    }

    private static function bookingOverlap(int $resourceId, string $startsAt, string $endsAt, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM resource_bookings
                WHERE resource_id = ? AND status IN ('Pending','Approved','Upcoming','Ongoing')
                AND starts_at < ? AND ends_at > ?";
        $params = [$resourceId, $endsAt, $startsAt];
        if ($ignoreId) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    private static function maintenanceRequest(int $requestId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT mr.*, a.asset_tag
             FROM maintenance_requests mr
             JOIN assets a ON a.id = mr.asset_id
             WHERE mr.id = ?'
        );
        $stmt->execute([$requestId]);
        return $stmt->fetch() ?: null;
    }

    private static function auditScopeAssets(int $departmentId, ?string $location): array
    {
        $sql = 'SELECT id FROM assets WHERE lifecycle_status NOT IN ("Disposed", "Retired")';
        $params = [];
        if ($departmentId > 0) {
            $sql .= ' AND department_id = ?';
            $params[] = $departmentId;
        }
        if ($location) {
            $sql .= ' AND location LIKE ?';
            $params[] = '%' . $location . '%';
        }
        $sql .= ' ORDER BY asset_tag LIMIT 500';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private static function auditAsset(int $auditAssetId): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM audit_assets WHERE id = ?');
        $stmt->execute([$auditAssetId]);
        return $stmt->fetch() ?: null;
    }

    private static function refreshBookingStates(): void
    {
        $pdo = Database::pdo();
        $pdo->exec("UPDATE resource_bookings SET status = 'Upcoming' WHERE status = 'Approved' AND starts_at > NOW()");
        $pdo->exec("UPDATE resource_bookings SET status = 'Ongoing' WHERE status IN ('Approved', 'Upcoming') AND starts_at <= NOW() AND ends_at > NOW()");
        $pdo->exec("UPDATE resource_bookings SET status = 'Completed' WHERE status IN ('Approved', 'Upcoming', 'Ongoing') AND ends_at <= NOW()");
    }

    private static function auditItemIsOpen(int $auditAssetId): bool
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM audit_assets aa JOIN audit_cycles ac ON ac.id = aa.audit_cycle_id WHERE aa.id = ? AND ac.status = "Open"');
        $stmt->execute([$auditAssetId]);
        return (int) $stmt->fetchColumn() === 1;
    }

    private static function isPrivilegedAuditor(int $userId): bool
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND r.slug IN ("admin", "asset_manager")');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn() === 1;
    }
}
