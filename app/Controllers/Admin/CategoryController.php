<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        $categories = new Category();
        $editId = (int) $this->input('edit');
        $this->view('admin/categories/index', [
            'title'      => 'Quản lý danh mục',
            'categories' => $categories->withProductCount(),
            'editing'    => $editId ? $categories->findById($editId) : null,
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireCsrf();
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->requireCsrf();
        $this->save((int) $id);
    }

    private function save(?int $id): void
    {
        $categories = new Category();
        $name = $this->input('name');
        $desc = $this->input('description');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            flash('error', 'Tên danh mục phải từ 2 đến 100 ký tự.');
            redirect('/admin/categories' . ($id ? "?edit=$id" : ''));
        }
        $slug = slugify($name);
        $base = $slug;
        for ($n = 2; $categories->slugExists($slug, (int) $id); $n++) {
            $slug = $base . '-' . $n;
        }
        $data = ['name' => $name, 'slug' => $slug, 'description' => $desc ?: null];
        if ($id) {
            $categories->findById($id) ?? $this->abort(404);
            $categories->update($id, $data);
        } else {
            $categories->create($data + ['is_active' => 1]);
        }
        flash('success', 'Đã lưu danh mục "' . $name . '".');
        redirect('/admin/categories');
    }

    public function toggle(string $id): void
    {
        $this->requireCsrf();
        (new Category())->toggle((int) $id);
        redirect('/admin/categories');
    }

    public function delete(string $id): void
    {
        $this->requireCsrf();
        $categories = new Category();
        if ($categories->productCount((int) $id) > 0) {
            flash('error', 'Chỉ xóa được danh mục không còn sản phẩm. Hãy chuyển hoặc xóa sản phẩm trước, hoặc tắt hiển thị danh mục.');
        } else {
            $categories->delete((int) $id);
            flash('success', 'Đã xóa danh mục.');
        }
        redirect('/admin/categories');
    }
}
