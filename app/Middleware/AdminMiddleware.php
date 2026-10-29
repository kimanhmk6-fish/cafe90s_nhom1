<?php
declare(strict_types=1);

namespace App\Middleware;

/** Chỉ tài khoản role = admin mới vào được khu vực /admin */
final class AdminMiddleware
{
    public static function requireAdmin(): void
    {
        $u = auth_user();
        if (!$u) {
            redirect('/admin/login');
        }
        if ($u['role'] !== 'admin') {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            exit;
        }
    }
}
