<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\ActivityLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (($_SERVER['REQUEST_URI'] ?? '') === '/' || str_ends_with(rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/'), 'odoo-atom')) {
            $this->redirect('/login');
        }

        if (empty($_SESSION['user']) && !empty($_COOKIE['assetflow_remember'])) {
            $remembered = User::findByRememberToken($_COOKIE['assetflow_remember']);
            if ($remembered) {
                $this->startUserSession($remembered);
            }
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

        $this->startUserSession($user);
        if (!empty($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));
            User::setRememberToken((int) $user['id'], $token);
            setcookie('assetflow_remember', $token, [
                'expires' => time() + 60 * 60 * 24 * 30,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
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
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if ($email) {
            $user = User::findByEmail((string) $email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                User::createPasswordReset((int) $user['id'], $token);
                $_SESSION['reset_link'] = $this->url('/reset-password?token=' . $token);
            }
        }
        $_SESSION['success'] = 'If the email exists, a reset link will be sent.';
        ActivityLog::write(null, 'Password reset requested', 'Authentication');
        $this->redirect('/forgot-password');
    }

    public function reset(): void
    {
        $this->view('auth/reset', ['title' => 'Reset password', 'csrf' => $this->csrf(), 'token' => $_GET['token'] ?? ''], 'auth');
    }

    public function updatePassword(): void
    {
        $this->verifyCsrf();
        $validator = (new Validator())->required($_POST, ['token' => 'Reset token', 'password' => 'Password'])->minLength($_POST, 'password', 8, 'Password');
        if ($validator->fails() || ($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
            $_SESSION['error'] = 'Password must be 8+ characters and match confirmation.';
            $this->redirect('/reset-password?token=' . urlencode($_POST['token'] ?? ''));
        }
        if (!User::resetPassword($_POST['token'], $_POST['password'])) {
            $_SESSION['error'] = 'Reset link is invalid or expired.';
            $this->redirect('/forgot-password');
        }
        $_SESSION['success'] = 'Password reset. Please sign in.';
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        ActivityLog::write($_SESSION['user']['id'] ?? null, 'Signed out', 'Authentication');
        if (!empty($_SESSION['user']['id'])) {
            User::setRememberToken((int) $_SESSION['user']['id'], null);
        }
        setcookie('assetflow_remember', '', time() - 3600, '/');
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }

    private function startUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'department_name' => $user['department_name'] ?? 'Unassigned',
            'role_slug' => $user['role_slug'],
            'role_name' => $user['role_name'],
        ];
    }
}
