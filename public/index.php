<?php

declare(strict_types=1);

$sessionPath = dirname(__DIR__) . '/tmp';
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}
session_start();

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\UserController;
use App\Core\Router;

$router = new Router();

$router->get('/', [AuthController::class, 'login']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/signup', [AuthController::class, 'signup']);
$router->post('/signup', [AuthController::class, 'register']);
$router->get('/forgot-password', [AuthController::class, 'forgot']);
$router->post('/forgot-password', [AuthController::class, 'sendReset']);
$router->get('/reset-password', [AuthController::class, 'reset']);
$router->post('/reset-password', [AuthController::class, 'updatePassword']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/403', [PageController::class, 'forbidden']);
$router->get('/404', [PageController::class, 'notFound']);

$router->get('/user/dashboard', [UserController::class, 'dashboard']);
$router->get('/user/assets', [UserController::class, 'assets']);
$router->get('/user/bookings', [UserController::class, 'bookings']);
$router->post('/user/bookings', [UserController::class, 'requestBooking']);
$router->get('/user/maintenance', [UserController::class, 'maintenance']);
$router->post('/user/maintenance', [UserController::class, 'requestMaintenance']);
$router->get('/user/transfers', [UserController::class, 'transfers']);
$router->post('/user/transfers', [UserController::class, 'requestTransfer']);
$router->get('/user/notifications', [UserController::class, 'notifications']);
$router->post('/user/notifications/read', [UserController::class, 'markNotificationsRead']);
$router->post('/user/notifications/read-one', [UserController::class, 'markNotificationRead']);
$router->post('/user/notifications/delete', [UserController::class, 'deleteNotification']);
$router->get('/user/profile', [UserController::class, 'profile']);
$router->post('/user/profile', [UserController::class, 'updateProfile']);
$router->get('/user/settings', [UserController::class, 'settings']);
$router->post('/user/settings/password', [UserController::class, 'changePassword']);

$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/organization', [AdminController::class, 'organization']);
$router->post('/admin/departments', [AdminController::class, 'storeDepartment']);
$router->post('/admin/categories', [AdminController::class, 'storeCategory']);
$router->post('/admin/employees', [AdminController::class, 'storeEmployee']);
$router->post('/admin/users/promote', [AdminController::class, 'promoteUser']);
$router->get('/admin/assets', [AdminController::class, 'assets']);
$router->post('/admin/assets', [AdminController::class, 'storeAsset']);
$router->get('/admin/allocations', [AdminController::class, 'allocations']);
$router->post('/admin/allocations', [AdminController::class, 'storeAllocation']);
$router->post('/admin/allocations/return', [AdminController::class, 'returnAllocation']);
$router->post('/admin/allocations/cancel', [AdminController::class, 'cancelAllocation']);
$router->post('/admin/transfers/decision', [AdminController::class, 'decideTransfer']);
$router->get('/admin/bookings', [AdminController::class, 'bookings']);
$router->post('/admin/bookings', [AdminController::class, 'storeBooking']);
$router->post('/admin/bookings/decision', [AdminController::class, 'decideBooking']);
$router->get('/admin/maintenance', [AdminController::class, 'maintenance']);
$router->post('/admin/maintenance/update', [AdminController::class, 'updateMaintenance']);
$router->get('/admin/audits', [AdminController::class, 'audits']);
$router->post('/admin/audits', [AdminController::class, 'storeAudit']);
$router->post('/admin/audits/item', [AdminController::class, 'updateAuditAsset']);
$router->post('/admin/audits/close', [AdminController::class, 'closeAudit']);
$router->get('/admin/reports', [AdminController::class, 'reports']);
$router->get('/admin/notifications', [AdminController::class, 'notifications']);
$router->post('/admin/notifications/read', [AdminController::class, 'markNotificationsRead']);
$router->post('/admin/notifications/read-one', [AdminController::class, 'markNotificationRead']);
$router->post('/admin/notifications/delete', [AdminController::class, 'deleteNotification']);
$router->get('/admin/profile', [AdminController::class, 'profile']);
$router->post('/admin/profile', [AdminController::class, 'updateProfile']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings/password', [AdminController::class, 'changePassword']);
$router->get('/admin/logs', [AdminController::class, 'logs']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
