<?php
declare(strict_types=1);

// ===== Hàm dùng chung (không chứa SQL) =====

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Thư mục gốc URL của website (hỗ trợ chạy trong thư mục con của XAMPP) */
function base_url(): string
{
    static $base = null;
    if ($base === null) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $dir = rtrim(dirname($script), '/');           // vd /cafe90snhom1/public
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        // Truy cập thẳng /public/... (không rewrite từ thư mục gốc) thì giữ nguyên /public
        $direct = $dir === '' || $uri === $dir || str_starts_with($uri, $dir . '/');
        $base = $direct ? $dir : preg_replace('#/public$#', '', $dir);
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Đường dẫn ảnh (ảnh mẫu trong assets hoặc ảnh upload) */
function img(?string $path, string $fallback = 'assets/images/placeholder.svg'): string
{
    $path = $path ?: $fallback;
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return url($path);
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_url();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    $path = preg_replace('#^/public(?=/|$)#', '', $path);
    return '/' . trim($path, '/');
}

function money($n): string
{
    return number_format((float) $n, 0, ',', '.') . 'đ';
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $from = 'àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ';
    $to   = 'aaaaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooooouuuuuuuuuuuyyyyyd';
    $map = [];
    foreach (mb_str_split($from) as $i => $ch) {
        $map[$ch] = $to[$i];
    }
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'item';
}

// ----- CSRF -----
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

// ----- Flash message & old input -----
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function with_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old_now'][$key] ?? $default;
}

// ----- Người dùng hiện tại -----
function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (auth_user()['role'] ?? '') === 'admin';
}

// ----- Nhãn trạng thái -----
function order_statuses(): array
{
    return [
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'preparing' => 'Đang pha chế',
        'shipping'  => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
}

function payment_statuses(): array
{
    return ['pending' => 'Chờ đối soát', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại'];
}

function status_badge(string $status): string
{
    $label = order_statuses()[$status] ?? $status;
    return '<span class="badge badge-' . e($status) . '">' . e($label) . '</span>';
}

function pay_badge(string $status): string
{
    $label = payment_statuses()[$status] ?? $status;
    return '<span class="badge badge-pay-' . e($status) . '">' . e($label) . '</span>';
}

function date_vn(?string $dt, string $fmt = 'd/m/Y H:i'): string
{
    return $dt ? date($fmt, strtotime($dt)) : '';
}

/** Chuỗi JSON an toàn để nhúng vào thuộc tính data-* */
function json_attr($data): string
{
    return e(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function avatar_url(?string $path): string
{
    return $path ? url($path) : asset('images/avatar-default.svg');
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name));
    return mb_strtoupper(mb_substr(end($parts) ?: '?', 0, 1) . (count($parts) > 1 ? mb_substr($parts[0], 0, 1) : ''));
}
