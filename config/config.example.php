<?php

return [
    'app_name' => 'ARMS',
    'base_url' => 'http://localhost/oddo/odoo-atom/public',
    'db' => [
        'host' => getenv('ASSETFLOW_DB_HOST') ?: '127.0.0.1',
        'name' => getenv('ASSETFLOW_DB_NAME') ?: 'arms',
        'user' => getenv('ASSETFLOW_DB_USER') ?: 'root',
        'pass' => getenv('ASSETFLOW_DB_PASS') ?: '',
        'charset' => getenv('ASSETFLOW_DB_CHARSET') ?: 'utf8mb4',
    ],
];
