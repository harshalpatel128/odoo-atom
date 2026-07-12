<?php

$baseUrl = getenv('ASSETFLOW_BASE_URL') ?: null;
if (!$baseUrl && PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $scriptDir;
}

return [
    'app_name' => 'ARMS',
    'base_url' => $baseUrl ?: 'http://localhost/oddo/odoo-atom/public',
    'db' => [
        'host' => getenv('ASSETFLOW_DB_HOST') ?: '127.0.0.1',
        'name' => getenv('ASSETFLOW_DB_NAME') ?: 'arms',
        'user' => getenv('ASSETFLOW_DB_USER') ?: 'root',
        'pass' => getenv('ASSETFLOW_DB_PASS') ?: '',
        'charset' => getenv('ASSETFLOW_DB_CHARSET') ?: 'utf8mb4',
    ],
];
