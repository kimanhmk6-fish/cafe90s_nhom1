<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// Khi chạy bằng "php -S localhost:8000 public/index.php": trả file tĩnh trực tiếp
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && is_file(__DIR__ . $path)) {
        return false;
    }
}

require BASE_PATH . '/app/bootstrap.php';
