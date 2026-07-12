<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\ActivityLog;
use App\Models\Dashboard;

final class UserController extends Controller
{
    public function dashboard(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/dashboard', [
            'title' => 'User Dashboard',
            'panel' => 'user',
            'counts' => Dashboard::counts(),
            'activities' => ActivityLog::recent(),
            'csrf' => $this->csrf(),
        ]);
    }

    public function assets(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/assets', ['title' => 'My Assets', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }

    public function bookings(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/bookings', ['title' => 'Resource Booking', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }

    public function maintenance(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/maintenance', ['title' => 'Maintenance Requests', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }

    public function transfers(): void
    {
        AuthMiddleware::requireLogin();
        $this->view('user/transfers', ['title' => 'Transfer Requests', 'panel' => 'user', 'csrf' => $this->csrf()]);
    }
}
