<?php
declare(strict_types=1);

use App\Core\Router;

// ---- Đọc .env ----
$envFile = is_file(BASE_PATH . '/.env') ? BASE_PATH . '/.env' : BASE_PATH . '/.env.example';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim(trim($v), "\"'");
}
function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

// ---- Autoload: App\Models\Product => app/Models/Product.php ----
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

require BASE_PATH . '/app/Helpers/functions.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
if (!defined('APP_TESTING')) {
    session_start();
    App\Middleware\AuthMiddleware::restoreRememberedLogin();
}

if (defined('APP_TESTING')) {
    return; // chế độ kiểm thử: chỉ nạp autoload + helper
}

$router = new Router();
require BASE_PATH . '/routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], request_path());
} catch (Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (env('APP_DEBUG') === 'true') {
        echo '<pre>' . e((string) $e) . '</pre>';
    } else {
        require BASE_PATH . '/app/Views/errors/500.php';
    }
}
