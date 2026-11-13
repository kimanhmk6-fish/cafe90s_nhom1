<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Client\AuthController as ClientAuth;
use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (is_admin()) {
            redirect('/admin');
        }
        $this->view('admin/login', ['title' => 'Đăng nhập quản trị'], 'none');
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = strtolower($this->input('email'));
        with_old(['email' => $email]);
        if (!ClientAuth::throttleOk()) {
            flash('error', 'Bạn nhập sai quá nhiều lần. Vui lòng thử lại sau ít phút.');
            redirect('/admin/login');
        }
        $user = (new User())->findByEmail($email);
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) {
            ClientAuth::throttleFail();
            flash('error', 'Email hoặc mật khẩu không đúng.');
            redirect('/admin/login');
        }
        if ($user['role'] !== 'admin' || !$user['is_active']) {
            flash('error', 'Chỉ tài khoản Admin đang hoạt động mới được truy cập khu vực quản trị.');
            redirect('/admin/login');
        }
        AuthMiddleware::login($user);
        redirect('/admin');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        AuthMiddleware::logout();
        redirect('/admin/login');
    }
}
