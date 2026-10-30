<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class Category extends BaseModel
{
    protected string $table = 'categories';
    protected array $fillable = ['name', 'slug', 'description', 'is_active'];

    public function active(): array
    {
        return $this->fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY id');
    }

    public function withProductCount(): array
    {
        return $this->fetchAll(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
             FROM categories c ORDER BY c.id'
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM categories WHERE slug = ?', [$slug]);
    }

    public function slugExists(string $slug, int $exceptId = 0): bool
    {
        return (bool) $this->fetchValue('SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?', [$slug, $exceptId]);
    }

    public function productCount(int $id): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM products WHERE category_id = ?', [$id]);
    }

    public function toggle(int $id): void
    {
        $this->run('UPDATE categories SET is_active = 1 - is_active WHERE id = ?', [$id]);
    }
}
