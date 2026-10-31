<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\PasswordReset;
use App\Models\User;

class AuthController extends Controller
{
    private const MAX_FAILS = 5;
    private const LOCK_SECONDS = 60;

    public function showLogin(): void
    {
        $this->view('client/auth/login', ['title' => 'Đăng nhập'], 'client');
    }

    public function login(): void
    {
        $this->requireCsrf();
        $email = strtolower($this->input('email'));
        $password = $_POST['password'] ?? '';
        with_old(['email' => $email]);

        if (!self::throttleOk()) {
            flash('error', 'Bạn nhập sai quá nhiều lần. Vui lòng thử lại sau ít phút.');
            redirect('/dang-nhap');
        }
        $user = (new User())->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            self::throttleFail();
            flash('error', 'Email hoặc mật khẩu không đúng.');
            redirect('/dang-nhap');
        }
        if (!$user['is_active']) {
            flash('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quán.');
            redirect('/dang-nhap');
        }
        unset($_SESSION['login_fails'], $_SESSION['login_lock']);
        AuthMiddleware::login($user);
        if (!empty($_POST['remember'])) {
            AuthMiddleware::remember((int) $user['id']);
        }
        if ($user['role'] === 'admin') {
            redirect('/admin');
        }
        $to = $_SESSION['intended'] ?? '/';
        unset($_SESSION['intended']);
        redirect(str_starts_with($to, '/') && !str_starts_with($to, '//') ? $to : '/');
    }

    public static function throttleOk(): bool
    {
        return ($_SESSION['login_lock'] ?? 0) < time();
    }

    public static function throttleFail(): void
    {
        $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
        if ($_SESSION['login_fails'] >= self::MAX_FAILS) {
            $_SESSION['login_lock'] = time() + self::LOCK_SECONDS;
            $_SESSION['login_fails'] = 0;
        }
    }

    public function showRegister(): void
    {
        $this->view('client/auth/register', ['title' => 'Đăng ký']);
    }

    public function register(): void
    {
        $this->requireCsrf();
        $name = $this->input('full_name');
        $email = strtolower($this->input('email'));
        $phone = preg_replace('/\s+/', '', $this->input('phone'));
        $pass = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        with_old(['full_name' => $name, 'email' => $email, 'phone' => $phone]);

        $errors = [];
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Họ tên phải từ 2 đến 100 ký tự.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $errors[] = 'Email không hợp lệ.';
        if (!preg_match('/^0\d{9,10}$/', $phone)) $errors[] = 'Số điện thoại phải gồm 10–11 chữ số, bắt đầu bằng 0.';
        if (strlen($pass) < 8) $errors[] = 'Mật khẩu tối thiểu 8 ký tự.';
        if ($pass !== $confirm) $errors[] = 'Mật khẩu nhập lại không khớp.';
        $users = new User();
        if (!$errors && $users->findByEmail($email)) $errors[] = 'Email này đã được đăng ký.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/dang-ky');
        }
        $id = $users->register($email, $pass, $name, $phone);
        AuthMiddleware::login($users->findById($id));
        flash('success', 'Chào mừng ' . $name . ' đến với Góc Cà Phê 90s!');
        redirect('/');
    }

    public function logout(): void
    {
        $this->requireCsrf();
        AuthMiddleware::logout();
        redirect('/');
    }

    // ----- Quên / đặt lại mật khẩu (không gửi mail: hiển thị liên kết để demo) -----
    public function showForgot(): void
    {
        $this->view('client/auth/forgot', ['title' => 'Quên mật khẩu']);
    }

    public function forgot(): void
    {
        $this->requireCsrf();
        $email = strtolower($this->input('email'));
        $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? (new User())->findByEmail($email) : null;
        if ($user && $user['is_active']) {
            $token = (new PasswordReset())->issue($email);
            // Môi trường học tập chưa có SMTP: hiển thị liên kết đặt lại trực tiếp (hiệu lực 30 phút)
            $_SESSION['reset_link'] = url('dat-lai-mat-khau') . '?email=' . urlencode($email) . '&token=' . $token;
        } else {
            unset($_SESSION['reset_link']);
        }
        flash('success', 'Nếu email tồn tại trong hệ thống, liên kết đặt lại mật khẩu đã được tạo.');
        redirect('/quen-mat-khau');
    }

    public function showReset(): void
    {
        $email = strtolower($this->input('email'));
        $token = $this->input('token');
        if (!(new PasswordReset())->findValid($email, $token)) {
            flash('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            redirect('/quen-mat-khau');
        }
        $this->view('client/auth/reset', ['title' => 'Đặt lại mật khẩu', 'email' => $email, 'token' => $token]);
    }

    public function reset(): void
    {
        $this->requireCsrf();
        $email = strtolower($this->input('email'));
        $token = $this->input('token');
        $pass = $_POST['password'] ?? '';
        $resets = new PasswordReset();
        if (!$resets->findValid($email, $token)) {
            flash('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            redirect('/quen-mat-khau');
        }
        if (strlen($pass) < 8 || $pass !== ($_POST['password_confirm'] ?? '')) {
            flash('error', 'Mật khẩu tối thiểu 8 ký tự và phải trùng khớp.');
            redirect('/dat-lai-mat-khau?email=' . urlencode($email) . '&token=' . urlencode($token));
        }
        $users = new User();
        $user = $users->findByEmail($email);
        $users->changePassword((int) $user['id'], $pass);
        $resets->forget($email);
        unset($_SESSION['reset_link']);
        flash('success', 'Đã đổi mật khẩu. Hãy đăng nhập lại.');
        redirect('/dang-nhap');
    }
}
