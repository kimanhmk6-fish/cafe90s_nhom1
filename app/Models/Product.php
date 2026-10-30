<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class Product extends BaseModel
{
    protected string $table = 'products';
    protected array $fillable = ['category_id', 'name', 'slug', 'description', 'image', 'base_price', 'is_featured', 'is_active'];

    /** Chênh lệch giá theo size (S/M/L) theo ERD: M +5.000, L +10.000 */
    public const SIZE_EXTRA = ['S' => 0, 'M' => 5000, 'L' => 10000];

    private const SELECT = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
        (SELECT COUNT(*) FROM product_sizes ps WHERE ps.product_id = p.id) AS size_count,
        COALESCE((SELECT MIN(ps.price) FROM product_sizes ps WHERE ps.product_id = p.id), p.base_price) AS min_price
        FROM products p JOIN categories c ON c.id = p.category_id";

    /**
     * Tìm kiếm + lọc + sắp xếp + phân trang.
     * $filters: q, category (slug), sort (price_asc|price_desc|name), only_public (bool)
     * @return array{0:array,1:int}
     */
    public function search(array $filters, int $limit, int $offset): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['only_public'])) {
            $where[] = 'p.is_active = 1 AND c.is_active = 1';
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $params[] = $params[] = '%' . $filters['q'] . '%';
        }
        if (($filters['category'] ?? '') !== '') {
            $where[] = 'c.slug = ?';
            $params[] = $filters['category'];
        }
        if (($filters['status'] ?? '') === 'hidden') {
            $where[] = 'p.is_active = 0';
        }
        $sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $order = match ($filters['sort'] ?? '') {
            'price_asc'  => 'min_price ASC, p.id',
            'price_desc' => 'min_price DESC, p.id',
            'name'       => 'p.name ASC',
            'id'         => 'p.id ASC',
            default      => 'p.is_featured DESC, p.id ASC',
        };
        $total = (int) $this->fetchValue(
            'SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id' . $sql,
            $params
        );
        $rows = $this->fetchAll(self::SELECT . $sql . " ORDER BY $order LIMIT $limit OFFSET $offset", $params);
        return [$this->attachSizes($rows), $total];
    }

    public function featured(int $limit = 6): array
    {
        $rows = $this->fetchAll(
            self::SELECT . " WHERE p.is_active = 1 AND c.is_active = 1 AND p.is_featured = 1 ORDER BY p.id LIMIT $limit"
        );
        return $this->attachSizes($rows);
    }

    public function detail(int $id): ?array
    {
        $row = $this->fetchOne(self::SELECT . ' WHERE p.id = ?', [$id]);
        return $row ? $this->attachSizes([$row])[0] : null;
    }

    public function sizesOf(int $productId): array
    {
        return $this->fetchAll(
            'SELECT s.id, s.name, s.description, ps.price FROM product_sizes ps
             JOIN sizes s ON s.id = ps.size_id WHERE ps.product_id = ? ORDER BY s.sort_order',
            [$productId]
        );
    }

    /** Gắn danh sách size vào từng sản phẩm (1 truy vấn cho cả trang) */
    private function attachSizes(array $rows): array
    {
        if (!$rows) {
            return [];
        }
        $ids = array_column($rows, 'id');
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $sizes = $this->fetchAll(
            "SELECT ps.product_id, s.id, s.name, ps.price FROM product_sizes ps
             JOIN sizes s ON s.id = ps.size_id WHERE ps.product_id IN ($marks) ORDER BY s.sort_order",
            $ids
        );
        $map = [];
        foreach ($sizes as $s) {
            $map[$s['product_id']][] = ['id' => (int) $s['id'], 'name' => $s['name'], 'price' => (int) $s['price']];
        }
        foreach ($rows as &$r) {
            $r['sizes'] = $map[$r['id']] ?? [];
        }
        return $rows;
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (bool) $this->fetchValue('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?', [$slug, $exceptId]);
    }

    /** Đồng bộ bảng product_sizes: có size => S/M/L tự tính từ giá gốc; không có => xóa hết */
    public function syncSizes(int $productId, bool $hasSize, int $basePrice): void
    {
        $this->run('DELETE FROM product_sizes WHERE product_id = ?', [$productId]);
        if (!$hasSize) {
            return;
        }
        foreach ((new Size())->ordered() as $size) {
            $this->run(
                'INSERT INTO product_sizes (product_id, size_id, price) VALUES (?, ?, ?)',
                [$productId, $size['id'], $basePrice + (self::SIZE_EXTRA[$size['name']] ?? 0)]
            );
        }
    }

    public function toggle(int $id, string $column): void
    {
        if (in_array($column, ['is_active', 'is_featured'], true)) {
            $this->run("UPDATE products SET $column = 1 - $column WHERE id = ?", [$id]);
        }
    }

    public function isInOrders(int $id): bool
    {
        return (bool) $this->fetchValue('SELECT COUNT(*) FROM order_details WHERE product_id = ?', [$id]);
    }

    /** Xóa hẳn nếu chưa từng được bán; nếu đã có trong đơn hàng thì chỉ ẩn */
    public function deleteOrHide(int $id): string
    {
        if ($this->isInOrders($id)) {
            $this->update($id, ['is_active' => 0]);
            return 'hidden';
        }
        $this->run('DELETE FROM cart_items WHERE product_id = ?', [$id]);
        $this->delete($id);
        return 'deleted';
    }

    public function stats(): array
    {
        return [
            'total'    => (int) $this->fetchValue('SELECT COUNT(*) FROM products'),
            'active'   => (int) $this->fetchValue('SELECT COUNT(*) FROM products WHERE is_active = 1'),
            'featured' => (int) $this->fetchValue('SELECT COUNT(*) FROM products WHERE is_featured = 1 AND is_active = 1'),
        ];
    }
}
