<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;

/** Kiểm tra đăng nhập + ghi nhớ đăng nhập */
final class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (!auth_user()) {
            $_SESSION['intended'] = request_path();
            flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('/dang-nhap');
        }
    }

    public static function requireGuest(): void
    {
        if (auth_user()) {
            redirect(is_admin() ? '/admin' : '/');
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $profile = (new User())->profileOf((int) $user['id']);
        $_SESSION['user'] = [
            'id'     => (int) $user['id'],
            'email'  => $user['email'],
            'role'   => $user['role'],
            'name'   => $profile['full_name'] ?? $user['email'],
            'avatar' => $profile['avatar'] ?? null,
        ];
    }

    public static function remember(int $userId): void
    {
        $token = bin2hex(random_bytes(24));
        (new User())->update($userId, ['remember_token' => hash('sha256', $token)]);
        setcookie('remember', $userId . ':' . $token, [
            'expires' => time() + 30 * 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
        ]);
    }

    public static function logout(): void
    {
        if ($u = auth_user()) {
            (new User())->update($u['id'], ['remember_token' => null]);
        }
        setcookie('remember', '', ['expires' => time() - 3600, 'path' => '/']);
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function restoreRememberedLogin(): void
    {
        if (auth_user() || empty($_COOKIE['remember']) || !str_contains($_COOKIE['remember'], ':')) {
            return;
        }
        [$id, $token] = explode(':', $_COOKIE['remember'], 2);
        $users = new User();
        $user = $users->findById((int) $id);
        if ($user && $user['is_active'] && $user['remember_token']
            && hash_equals($user['remember_token'], hash('sha256', $token))) {
            self::login($user);
        }
    }
}
