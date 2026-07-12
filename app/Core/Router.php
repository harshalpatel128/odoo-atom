<?php

namespace App\Core;

final class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($uri);
        $handler = null;

        if (isset($this->routes[$method]) && isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
        }

        if (!$handler) {
            http_response_code(404);
            $errorView = __DIR__ . '/../Views/errors/plain-404.php';
            if (is_file($errorView)) {
                require $errorView;
                return;
            }
            echo '404 - Page not found';
            return;
        }

        [$class, $action] = $handler;
        (new $class())->$action();
    }

    private function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if (!$path) {
            return '/';
        }

        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $projectDir = preg_replace('#/public$#', '', $scriptDir) ?: '';
        $baseCandidates = array_values(array_filter(array_unique([$scriptDir, $projectDir])));

        foreach ($baseCandidates as $base) {
            if ($base !== '/' && $this->startsWith($path, $base)) {
                $path = substr($path, strlen($base)) ?: '/';
                break;
            }
        }

        $path = '/' . ltrim($path, '/');
        $path = preg_replace('#^/public(?=/|$)#', '', $path) ?: '/';
        $path = preg_replace('#/index\.php$#', '/', $path) ?: '/';

        return $path !== '/' ? rtrim($path, '/') : '/';
    }

    private function startsWith(string $value, string $prefix): bool
    {
        return substr($value, 0, strlen($prefix)) === $prefix;
    }
}
