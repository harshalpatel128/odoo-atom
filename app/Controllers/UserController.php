<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Uploader;
use App\Core\Validator;
use App\Middleware\AuthMiddleware;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Dashboard;
use App\Models\Notification;
use App\Models\User;
use App\Models\Workflow;

final class UserController extends Controller
{
    public function dashboard(): void
    {
        AuthMiddleware::requireLogin();
        Workflow::createBookingReminders((int) $_SESSION['user']['id']);
        Workflow::createOverdueAllocationNotifications((int) $_SESSION['user']['id']);
        $this->view('user/dashboard', [
            'title' => 'User Dashboard',
            'panel' => 'user',
            'counts' => Dashboard::counts(),
            'charts' => Dashboard::charts(),
            'latest' => Dashboard::latest(),
            'activities' => ActivityLog::recent(),
            'csrf' => $this->csrf(),
        ]);
    }

    public function assets(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/assets', ['title' => 'My Assets', 'panel' => 'user', 'assets' => Asset::assignedTo((int) $_SESSION['user']['id']), 'csrf' => $this->csrf()]);
    }

    public function bookings(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/bookings', ['title' => 'Resource Booking', 'panel' => 'user', 'resources' => Workflow::resources(), 'bookings' => Workflow::bookings((int) $_SESSION['user']['id']), 'csrf' => $this->csrf()]);
    }

    public function maintenance(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/maintenance', ['title' => 'Maintenance Requests', 'panel' => 'user', 'assets' => Asset::assignedTo((int) $_SESSION['user']['id']), 'requests' => Workflow::maintenanceRequests((int) $_SESSION['user']['id']), 'csrf' => $this->csrf()]);
    }

    public function transfers(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/transfers', ['title' => 'Transfer Requests', 'panel' => 'user', 'assets' => Asset::assignedTo((int) $_SESSION['user']['id']), 'users' => User::allWithRoles(), 'transfers' => Workflow::transfers((int) $_SESSION['user']['id']), 'csrf' => $this->csrf()]);
    }

    public function audits(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/audits', ['title' => 'My Audit Tasks', 'panel' => 'user', 'auditAssets' => Workflow::auditAssetsForAuditor((int) $_SESSION['user']['id']), 'csrf' => $this->csrf()]);
    }

    public function updateAuditAsset(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        try {
            Workflow::updateAuditAsset($_POST, (int) $_SESSION['user']['id']);
            ActivityLog::write((int) $_SESSION['user']['id'], 'Verified audit asset', 'Audits');
            $_SESSION['success'] = 'Audit item updated.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/user/audits');
    }

    public function notifications(): void
    {
        AuthMiddleware::requireLogin();
        $userId = (int) $_SESSION['user']['id'];
        $this->view('shared/notifications', ['title' => 'Notifications', 'panel' => 'user', 'notifications' => Notification::forUser($userId), 'unreadCount' => Notification::unreadCount($userId), 'csrf' => $this->csrf()]);
    }

    public function markNotificationsRead(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        Notification::markAllRead((int) $_SESSION['user']['id']);
        $this->redirect('/user/notifications');
    }

    public function markNotificationRead(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        Notification::markRead((int) $_POST['notification_id'], (int) $_SESSION['user']['id']);
        $this->redirect('/user/notifications');
    }

    public function deleteNotification(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        Notification::delete((int) $_POST['notification_id'], (int) $_SESSION['user']['id']);
        $this->redirect('/user/notifications');
    }

    public function profile(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('shared/profile', ['title' => 'Profile', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }

    public function settings(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('shared/settings', ['title' => 'Settings', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }

    public function updateProfile(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['name' => 'Name']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
        } else {
            User::updateProfile((int) $_SESSION['user']['id'], $_POST);
            $_SESSION['user'] = User::find((int) $_SESSION['user']['id']);
            $_SESSION['success'] = 'Profile updated.';
        }
        $this->redirect('/user/profile');
    }

    public function changePassword(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        if (strlen($_POST['new_password'] ?? '') < 8 || ($_POST['new_password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
            $_SESSION['error'] = 'New passwords must match and be at least 8 characters.';
        } elseif (User::changePassword((int) $_SESSION['user']['id'], $_POST['current_password'] ?? '', $_POST['new_password'])) {
            $_SESSION['success'] = 'Password updated.';
        } else {
            $_SESSION['error'] = 'Current password is incorrect.';
        }
        $this->redirect('/user/settings');
    }

    public function requestBooking(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        $validator = (new Validator())
            ->required($_POST, ['resource_id' => 'Resource', 'starts_at' => 'Start', 'ends_at' => 'End'])
            ->dateOrder($_POST, 'starts_at', 'ends_at');
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
        } elseif (!Workflow::bookResource($_POST, (int) $_SESSION['user']['id'])) {
            $_SESSION['error'] = 'Resource is already booked for that time window.';
        } else {
            ActivityLog::write($_SESSION['user']['id'], 'Requested booking', 'Bookings');
            $_SESSION['success'] = 'Booking requested.';
        }
        $this->redirect('/user/bookings');
    }

    public function requestMaintenance(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['asset_id' => 'Asset', 'description' => 'Description']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
            $this->redirect('/user/maintenance');
        }
        try {
            $photoPath = Uploader::optional('photo', 'image', 'maintenance');
            Workflow::maintenance($_POST, (int) $_SESSION['user']['id'], $photoPath);
            ActivityLog::write($_SESSION['user']['id'], 'Requested maintenance', 'Maintenance');
            $_SESSION['success'] = 'Maintenance request submitted.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/user/maintenance');
    }

    public function requestTransfer(): void
    {
        AuthMiddleware::requireLogin();
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['asset_id' => 'Asset', 'to_user_id' => 'Transfer recipient']);
        if ($validator->fails()) {
            $_SESSION['error'] = reset($validator->errors());
        } else {
            try {
                Workflow::requestTransfer($_POST, (int) $_SESSION['user']['id']);
                ActivityLog::write($_SESSION['user']['id'], 'Requested transfer', 'Transfers');
                $_SESSION['success'] = 'Transfer request submitted.';
            } catch (\Throwable $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
        $this->redirect('/user/transfers');
    }
}
