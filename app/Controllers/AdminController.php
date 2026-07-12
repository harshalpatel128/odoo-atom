<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Uploader;
use App\Core\Validator;
use App\Middleware\AuthMiddleware;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Dashboard;
use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workflow;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        AuthMiddleware::requireAdmin();
        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'panel' => 'admin',
            'counts' => Dashboard::counts(),
            'charts' => Dashboard::charts(),
            'latest' => Dashboard::latest(),
            'activities' => ActivityLog::recent(),
            'csrf' => $this->csrf(),
        ]);
    }

    public function organization(): void
    {
        AuthMiddleware::requireAdmin();
        $this->view('admin/organization', [
            'title' => 'Organization Setup',
            'panel' => 'admin',
            'users' => User::allWithRoles(),
            'departments' => Department::all(),
            'categories' => AssetCategory::all(),
            'csrf' => $this->csrf(),
        ]);
    }

    public function promoteUser(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        $role = $_POST['role'] ?? 'employee';
        if (in_array($role, ['employee', 'department_head', 'asset_manager'], true)) {
            User::promote((int) $_POST['user_id'], $role);
            ActivityLog::write($_SESSION['user']['id'], 'Promoted user to ' . $role, 'Users');
            $_SESSION['success'] = 'User role updated.';
        }
        $this->redirect('/admin/organization');
    }

    public function storeDepartment(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['name' => 'Department name']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
        } else {
            Department::create($_POST);
            ActivityLog::write($_SESSION['user']['id'], 'Created department', 'Departments');
            $_SESSION['success'] = 'Department saved.';
        }
        $this->redirect('/admin/organization');
    }

    public function storeCategory(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['name' => 'Category name']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
        } else {
            AssetCategory::create($_POST);
            ActivityLog::write($_SESSION['user']['id'], 'Created category', 'Categories');
            $_SESSION['success'] = 'Category saved.';
        }
        $this->redirect('/admin/organization');
    }

    public function storeEmployee(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['name' => 'Name', 'email' => 'Email'])->email($_POST, 'email', 'Email');
        if ($validator->fails() || User::findByEmail(trim($_POST['email'] ?? ''))) {
            $_SESSION['error'] = $validator->fails() ? reset($validator->errors()) : 'Email is already registered.';
        } else {
            User::create($_POST);
            ActivityLog::write($_SESSION['user']['id'], 'Created employee', 'Users');
            $_SESSION['success'] = 'Employee saved.';
        }
        $this->redirect('/admin/organization');
    }

    public function assets(): void
    {
        AuthMiddleware::requireAdmin();
        $this->view('admin/assets', [
            'title' => 'Asset Directory',
            'panel' => 'admin',
            'assets' => Asset::all(),
            'categories' => AssetCategory::all(),
            'departments' => Department::all(),
            'users' => User::allWithRoles(),
            'csrf' => $this->csrf(),
        ]);
    }

    public function storeAsset(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['name' => 'Asset name', 'category_id' => 'Category']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
            $this->redirect('/admin/assets');
        }
        try {
            $_POST['photo_path'] = Uploader::optional('photo', 'image', 'assets');
            $assetId = Asset::create($_POST);
            $documentPath = Uploader::optional('document', 'document', 'documents');
            if ($documentPath) {
                \App\Core\Database::pdo()->prepare('INSERT INTO asset_documents (asset_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)')
                    ->execute([$assetId, basename($documentPath), $documentPath, $_SESSION['user']['id']]);
            }
            ActivityLog::write($_SESSION['user']['id'], 'Created asset', 'Assets');
            $_SESSION['success'] = 'Asset saved.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/assets');
    }

    public function allocations(): void { AuthMiddleware::requireAdmin(); $this->simple('admin/allocations', 'Allocation & Transfers', ['allocations' => Workflow::allocations(), 'transfers' => Workflow::transfers()]); }
    public function bookings(): void { AuthMiddleware::requireAdmin(); $this->simple('admin/bookings', 'Resource Bookings', ['bookings' => Workflow::bookings()]); }
    public function maintenance(): void { AuthMiddleware::requireAdmin(); $this->simple('admin/maintenance', 'Maintenance Management', ['requests' => Workflow::maintenanceRequests()]); }
    public function audits(): void { AuthMiddleware::requireAdmin(); $this->simple('admin/audits', 'Asset Audit', ['audits' => Workflow::audits(), 'auditAssets' => Workflow::auditAssets()]); }
    public function reports(): void { AuthMiddleware::requireAdmin(); $this->simple('admin/reports', 'Reports & Analytics', ['reports' => \App\Models\Report::datasets($_GET)]); }
    public function notifications(): void { AuthMiddleware::requireAdmin(); $this->simple('shared/notifications', 'Notifications', ['notifications' => Notification::all(), 'unreadCount' => Notification::unreadCount((int) $_SESSION['user']['id'])]); }
    public function profile(): void { AuthMiddleware::requireAdmin(); $this->simple('shared/profile', 'Profile'); }
    public function settings(): void { AuthMiddleware::requireAdmin(); $this->simple('shared/settings', 'Settings'); }
    public function logs(): void { AuthMiddleware::requireAdmin(); $this->view('admin/logs', ['title' => 'Activity Logs', 'panel' => 'admin', 'activities' => ActivityLog::recent(50), 'csrf' => $this->csrf()]); }

    public function updateProfile(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['name' => 'Name']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
        } else {
            User::updateProfile((int) $_SESSION['user']['id'], $_POST);
            $_SESSION['user'] = User::find((int) $_SESSION['user']['id']);
            $_SESSION['success'] = 'Profile updated.';
        }
        $this->redirect('/admin/profile');
    }

    public function changePassword(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        if (strlen($_POST['new_password'] ?? '') < 8 || ($_POST['new_password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
            $_SESSION['error'] = 'New passwords must match and be at least 8 characters.';
        } elseif (User::changePassword((int) $_SESSION['user']['id'], $_POST['current_password'] ?? '', $_POST['new_password'])) {
            $_SESSION['success'] = 'Password updated.';
        } else {
            $_SESSION['error'] = 'Current password is incorrect.';
        }
        $this->redirect('/admin/settings');
    }

    public function storeAllocation(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::allocate($_POST, (int) $_SESSION['user']['id']);
            ActivityLog::write($_SESSION['user']['id'], 'Allocated asset', 'Allocations');
            $_SESSION['success'] = 'Asset allocated.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/allocations');
    }

    public function returnAllocation(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::returnAllocation($_POST, (int) $_SESSION['user']['id']);
            ActivityLog::write($_SESSION['user']['id'], 'Returned asset', 'Allocations');
            $_SESSION['success'] = 'Allocation returned.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/allocations');
    }

    public function cancelAllocation(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::cancelAllocation((int) $_POST['allocation_id'], (int) $_SESSION['user']['id']);
            ActivityLog::write($_SESSION['user']['id'], 'Cancelled allocation', 'Allocations');
            $_SESSION['success'] = 'Allocation cancelled.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/allocations');
    }

    public function decideTransfer(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::decideTransfer((int) $_POST['transfer_id'], $_POST['status'], (int) $_SESSION['user']['id']);
            ActivityLog::write($_SESSION['user']['id'], 'Updated transfer to ' . $_POST['status'], 'Transfers');
            $_SESSION['success'] = 'Transfer updated.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/allocations');
    }

    public function storeBooking(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            if (!Workflow::bookResource($_POST, (int) ($_POST['user_id'] ?: $_SESSION['user']['id']))) {
                $_SESSION['error'] = 'Resource is already booked for that time window.';
            } else {
                ActivityLog::write($_SESSION['user']['id'], 'Created booking', 'Bookings');
                $_SESSION['success'] = 'Booking created.';
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/bookings');
    }

    public function decideBooking(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::decideBooking((int) $_POST['booking_id'], $_POST['status'], (int) $_SESSION['user']['id']);
            $_SESSION['success'] = 'Booking updated.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/bookings');
    }

    public function updateMaintenance(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::updateMaintenance($_POST, (int) $_SESSION['user']['id']);
            ActivityLog::write($_SESSION['user']['id'], 'Updated maintenance', 'Maintenance');
            $_SESSION['success'] = 'Maintenance request updated.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/maintenance');
    }

    public function storeAudit(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::createAudit($_POST, (int) $_SESSION['user']['id']);
            $_SESSION['success'] = 'Audit cycle created.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/audits');
    }

    public function updateAuditAsset(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        try {
            Workflow::updateAuditAsset($_POST, (int) $_SESSION['user']['id']);
            ActivityLog::write($_SESSION['user']['id'], 'Updated audit asset', 'Audits');
            $_SESSION['success'] = 'Audit item updated.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/audits');
    }

    public function closeAudit(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        Workflow::closeAudit((int) $_POST['audit_cycle_id'], (int) $_SESSION['user']['id']);
        $_SESSION['success'] = 'Audit cycle closed.';
        $this->redirect('/admin/audits');
    }

    public function markNotificationsRead(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        Notification::markAllRead((int) $_SESSION['user']['id']);
        $this->redirect('/admin/notifications');
    }

    public function markNotificationRead(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        Notification::markRead((int) $_POST['notification_id'], (int) $_SESSION['user']['id'], true);
        $this->redirect('/admin/notifications');
    }

    public function deleteNotification(): void
    {
        AuthMiddleware::requireAdmin();
        $this->verifyCsrf();
        Notification::delete((int) $_POST['notification_id'], (int) $_SESSION['user']['id'], true);
        $this->redirect('/admin/notifications');
    }

    private function simple(string $view, string $title, array $extra = []): void
    {
        $this->view($view, array_merge([
            'title' => $title,
            'panel' => 'admin',
            'assets' => Asset::all(100),
            'departments' => Department::all(),
            'users' => User::allWithRoles(),
            'resources' => Workflow::resources(),
            'csrf' => $this->csrf(),
        ], $extra));
    }
}
