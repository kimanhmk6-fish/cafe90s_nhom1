<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Pagination;
use App\Models\User;

class UserController extends Controller
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        $users = new User();
        $q = $this->input('q');
        $filter = $this->input('filter');
        [, $total] = $users->search($q, $filter, 1, 0);
        $pager = Pagination::fromRequest($total, self::PER_PAGE);
        [$items] = $users->search($q, $filter, self::PER_PAGE, $pager->offset());
        $this->view('admin/users/index', ['title' => 'Quản lý người dùng', 'items' => $items, 'pager' => $pager, 'q' => $q, 'filter' => $filter], 'admin');
    }

    /** Admin không thể khóa / hạ quyền chính mình hay Admin khác */
    private function targetCustomer(string $id): array
    {
        $user = (new User())->findById((int) $id) ?? $this->abort(404);
        if ($user['role'] === 'admin' || (int) $user['id'] === auth_user()['id']) {
            flash('error', 'Không thể thao tác với tài khoản của chính mình hoặc của Admin khác.');
            redirect('/admin/users');
        }
        return $user;
    }

    public function toggleActive(string $id): void
    {
        $this->requireCsrf();
        $user = $this->targetCustomer($id);
        (new User())->update((int) $id, ['is_active' => $user['is_active'] ? 0 : 1, 'remember_token' => null]);
        flash('success', $user['is_active'] ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
        $this->back('/admin/users');
    }

    public function promote(string $id): void
    {
        $this->requireCsrf();
        $this->targetCustomer($id);
        (new User())->update((int) $id, ['role' => 'admin']);
        flash('success', 'Đã nâng tài khoản lên Admin.');
        $this->back('/admin/users');
    }
}
