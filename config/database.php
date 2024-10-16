<?php

return [
    'mysql' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_NAME', 'framework'),
        'username' => env('DB_USER', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ]
];
