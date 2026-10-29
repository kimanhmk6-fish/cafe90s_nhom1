<?php
declare(strict_types=1);

namespace App\Core;

/** Lớp cha của Controller: render View, trả JSON, kiểm tra CSRF */
abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'client'): void
    {
        $flashes = pull_flash();
        $_SESSION['_old_now'] = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        if (auth_user() && $layout === 'client') {
            $data['cartCount'] = (new \App\Models\Cart())->count(auth_user()['id']);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . "/app/Views/{$view}.php";
        $content = ob_get_clean();

        if ($layout === 'none') {
            echo $content;
            return;
        }
        require BASE_PATH . "/app/Views/layouts/{$layout}.php";
    }

    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function requireCsrf(bool $json = false): void
    {
        if (!verify_csrf()) {
            if ($json) {
                $this->json(['ok' => false, 'message' => 'Phiên làm việc hết hạn, vui lòng tải lại trang.'], 419);
            }
            flash('error', 'Phiên làm việc hết hạn, vui lòng thử lại.');
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    protected function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        $same = $ref !== '' && parse_url($ref, PHP_URL_HOST) === parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
        redirect($same ? $ref : $fallback);
    }

    protected function abort(int $code): never
    {
        http_response_code($code);
        require BASE_PATH . "/app/Views/errors/{$code}.php";
        exit;
    }

    protected function input(string $key, string $default = ''): string
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }
}
