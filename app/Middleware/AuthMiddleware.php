<?php

namespace App\Middleware;

final class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . self::baseUrl() . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (($_SESSION['user']['role_slug'] ?? '') !== 'admin') {
            header('Location: ' . self::baseUrl() . '/user/dashboard');
            exit;
        }
    }

    public static function requireAnyRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array($_SESSION['user']['role_slug'] ?? '', $roles, true)) {
            header('Location: ' . self::baseUrl() . '/403');
            exit;
        }
    }

    private static function baseUrl(): string
    {
        $config = require __DIR__ . '/../../config/config.php';
        return rtrim($config['base_url'], '/');
    }
}
