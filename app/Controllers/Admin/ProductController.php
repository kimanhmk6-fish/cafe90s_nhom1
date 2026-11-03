<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Pagination;
use App\Helpers\UploadHelper;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): void
    {
        $products = new Product();
        $filters = ['q' => $this->input('q'), 'category' => $this->input('category'), 'status' => $this->input('status')];
        [, $total] = $products->search($filters, 1, 0);
        $pager = Pagination::fromRequest($total, self::PER_PAGE);
        [$items] = $products->search($filters + ['sort' => 'id'], self::PER_PAGE, $pager->offset());
        $this->view('admin/products/index', [
            'title' => 'Quản lý sản phẩm', 'items' => $items, 'pager' => $pager, 'filters' => $filters,
            'stats' => $products->stats(), 'categories' => (new Category())->all('id'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->form(['is_active' => 1, 'base_price' => '', 'sizes' => []], 'Thêm sản phẩm');
    }

    public function edit(string $id): void
    {
        $p = (new Product())->detail((int) $id) ?? $this->abort(404);
        $this->form($p, 'Sửa sản phẩm');
    }

    private function form(array $product, string $title): void
    {
        $this->view('admin/products/form', [
            'title' => $title, 'product' => $product, 'categories' => (new Category())->withProductCount(),
            'extra' => Product::SIZE_EXTRA,
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
        $products = new Product();
        $current = $id ? ($products->findById($id) ?? $this->abort(404)) : null;
        $back = $id ? "/admin/products/$id/edit" : '/admin/products/create';

        $name = $this->input('name');
        $price = (int) preg_replace('/\D/', '', $this->input('base_price'));
        $categoryId = (int) $this->input('category_id');
        $hasSize = !empty($_POST['has_size']);
        $desc = $this->input('description');
        $slug = slugify($this->input('slug') ?: $name);
        with_old($_POST);

        $errors = [];
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) $errors[] = 'Tên món phải từ 2 đến 150 ký tự.';
        if ($price < 1000 || $price > 10000000) $errors[] = 'Giá gốc phải từ 1.000đ đến 10.000.000đ.';
        if (!(new Category())->findById($categoryId)) $errors[] = 'Vui lòng chọn danh mục.';
        if (mb_strlen($desc) > 1000) $errors[] = 'Mô tả tối đa 1000 ký tự.';
        $base = $slug;
        for ($n = 2; $products->slugExists($slug, (int) $id); $n++) {
            $slug = $base . '-' . $n;
        }
        $up = UploadHelper::image($_FILES['image'] ?? [], 'products');
        if ($up['error']) $errors[] = $up['error'];
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect($back);
        }

        $data = [
            'category_id' => $categoryId, 'name' => $name, 'slug' => $slug, 'description' => $desc ?: null,
            'base_price' => $price, 'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
        ];
        if ($up['path']) {
            $data['image'] = $up['path'];
            if ($current) UploadHelper::remove($current['image']);
        }
        if ($id) {
            $products->update($id, $data);
        } else {
            $id = $products->create($data);
        }
        $products->syncSizes($id, $hasSize, $price);
        flash('success', 'Đã lưu sản phẩm "' . $name . '".');
        redirect('/admin/products');
    }

    public function toggle(string $id): void
    {
        $this->requireCsrf();
        (new Product())->toggle((int) $id, $this->input('field') === 'featured' ? 'is_featured' : 'is_active');
        $this->back('/admin/products');
    }

    public function delete(string $id): void
    {
        $this->requireCsrf();
        $products = new Product();
        $p = $products->findById((int) $id) ?? $this->abort(404);
        $result = $products->deleteOrHide((int) $id);
        if ($result === 'deleted') {
            UploadHelper::remove($p['image']);
            flash('success', 'Đã xóa sản phẩm.');
        } else {
            flash('info', 'Sản phẩm đã có trong đơn hàng nên được chuyển sang trạng thái ẩn thay vì xóa.');
        }
        $this->back('/admin/products');
    }
}
