<?php
declare(strict_types=1);

namespace App\Core;

use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** Router tự xây: khớp URL + method, chạy middleware rồi gọi Controller@method */
final class Router
{
    private array $routes = [];

    public function get(string $path, string $handler, ?string $guard = null): void
    {
        $this->add('GET', $path, $handler, $guard);
    }

    public function post(string $path, string $handler, ?string $guard = null): void
    {
        $this->add('POST', $path, $handler, $guard);
    }

    private function add(string $method, string $path, string $handler, ?string $guard): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[] = compact('method', 'regex', 'handler', 'guard');
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $r) {
            if ($r['method'] !== $method || !preg_match($r['regex'], $path, $m)) {
                continue;
            }
            match ($r['guard']) {
                'auth'  => AuthMiddleware::requireLogin(),
                'admin' => AdminMiddleware::requireAdmin(),
                'guest' => AuthMiddleware::requireGuest(),
                default => null,
            };
            [$class, $action] = explode('@', $r['handler']);
            $class = 'App\\Controllers\\' . $class;
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            (new $class())->$action(...array_values($params));
            return;
        }
        http_response_code(404);
        require BASE_PATH . '/app/Views/errors/404.php';
    }
}
