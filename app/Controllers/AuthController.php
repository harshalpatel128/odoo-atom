<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (($_SERVER['REQUEST_URI'] ?? '') === '/' || str_ends_with(rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/'), 'odoo-atom')) {
            $this->redirect('/login');
        }

        if (!empty($_SESSION['user'])) {
            $this->redirect(($_SESSION['user']['role_slug'] === 'admin') ? '/admin/dashboard' : '/user/dashboard');
        }
        $this->view('auth/login', ['title' => 'Sign in', 'csrf' => $this->csrf()], 'auth');
    }

    public function authenticate(): void
    {
        $this->verifyCsrf();
        $user = User::findByEmail(trim($_POST['email'] ?? ''));
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password_hash']) || $user['status'] !== 'active') {
            $_SESSION['error'] = 'Invalid email, password, or inactive account.';
            $this->redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_slug' => $user['role_slug'],
            'role_name' => $user['role_name'],
        ];
        ActivityLog::write((int) $user['id'], 'Signed in', 'Authentication');
        $this->redirect($user['role_slug'] === 'admin' ? '/admin/dashboard' : '/user/dashboard');
    }

    public function signup(): void
    {
        $this->view('auth/signup', ['title' => 'Create employee account', 'csrf' => $this->csrf()], 'auth');
    }

    public function register(): void
    {
        $this->verifyCsrf();
        $name = trim($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        if (!$name || !$email || strlen($password) < 8 || $password !== ($_POST['password_confirmation'] ?? '')) {
            $_SESSION['error'] = 'Please enter valid details. Password must be 8+ characters and match confirmation.';
            $this->redirect('/signup');
        }
        if (User::findByEmail((string) $email)) {
            $_SESSION['error'] = 'Email is already registered.';
            $this->redirect('/signup');
        }

        $id = User::createEmployee($name, (string) $email, $password);
        ActivityLog::write($id, 'Employee signup', 'Authentication');
        $_SESSION['success'] = 'Account created. Please sign in.';
        $this->redirect('/login');
    }

    public function forgot(): void
    {
        $this->view('auth/forgot', ['title' => 'Forgot password', 'csrf' => $this->csrf()], 'auth');
    }

    public function sendReset(): void
    {
        $this->verifyCsrf();
        $_SESSION['success'] = 'If the email exists, a reset link will be sent.';
        ActivityLog::write(null, 'Password reset requested', 'Authentication');
        $this->redirect('/forgot-password');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        ActivityLog::write($_SESSION['user']['id'] ?? null, 'Signed out', 'Authentication');
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }
}
