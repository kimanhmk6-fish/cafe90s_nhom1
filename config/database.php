<?php
// Cấu hình kết nối CSDL, đọc từ file .env (xem .env.example)
return [
    'host'    => env('DB_HOST', '127.0.0.1'),
    'port'    => env('DB_PORT', '3306'),
    'name'    => env('DB_NAME', 'cafe90snhom1'),
    'user'    => env('DB_USER', 'root'),
    'pass'    => env('DB_PASS', ''),
    'charset' => 'utf8mb4',
];
