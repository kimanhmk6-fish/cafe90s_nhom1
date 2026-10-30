<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class Cart extends BaseModel
{
    protected string $table = 'carts';
    protected array $fillable = ['user_id'];
    public const MAX_QTY = 20;

    public function idOf(int $userId): int
    {
        $id = $this->fetchValue('SELECT id FROM carts WHERE user_id = ?', [$userId]);
        return $id ? (int) $id : $this->create(['user_id' => $userId]);
    }

    /** Dòng giỏ hàng kèm tên, ảnh, giá hiện hành (giá theo size hoặc giá gốc) */
    public function items(int $userId): array
    {
        return $this->fetchAll(
            'SELECT ci.id, ci.product_id, ci.size_id, ci.quantity, p.name, p.image, p.is_active,
                    s.name AS size_name,
                    COALESCE(ps.price, p.base_price) AS price
             FROM cart_items ci
             JOIN carts c ON c.id = ci.cart_id
             JOIN products p ON p.id = ci.product_id
             LEFT JOIN sizes s ON s.id = ci.size_id
             LEFT JOIN product_sizes ps ON ps.product_id = ci.product_id AND ps.size_id = ci.size_id
             WHERE c.user_id = ? ORDER BY ci.id',
            [$userId]
        );
    }

    public function totals(array $items): array
    {
        $subtotal = 0;
        $count = 0;
        foreach ($items as $i) {
            $subtotal += (int) $i['price'] * (int) $i['quantity'];
            $count += (int) $i['quantity'];
        }
        $fee = $items ? (int) env('SHIPPING_FEE', 15000) : 0;
        return ['subtotal' => $subtotal, 'shipping' => $fee, 'total' => $subtotal + $fee, 'count' => $count];
    }

    public function count(int $userId = 0): int
    {
        return (int) $this->fetchValue(
            'SELECT COALESCE(SUM(ci.quantity), 0) FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?',
            [$userId]
        );
    }

    /** Thêm sản phẩm. Trả về chuỗi lỗi hoặc null nếu thành công */
    public function add(int $userId, int $productId, ?int $sizeId, int $qty): ?string
    {
        $product = (new Product())->detail($productId);
        if (!$product || !$product['is_active'] || !$product['category_slug']) {
            return 'Món này hiện không còn bán.';
        }
        $validSizes = array_column($product['sizes'], 'id');
        if ($validSizes) {
            if (!$sizeId || !in_array($sizeId, $validSizes, true)) {
                return 'Vui lòng chọn size.';
            }
        } else {
            $sizeId = null; // bánh / đồ ăn không có size
        }
        $qty = max(1, min(self::MAX_QTY, $qty));
        $cartId = $this->idOf($userId);
        $existing = $this->fetchOne(
            'SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND size_id <=> ?',
            [$cartId, $productId, $sizeId]
        );
        if ($existing) {
            $newQty = min(self::MAX_QTY, $existing['quantity'] + $qty);
            $this->run('UPDATE cart_items SET quantity = ? WHERE id = ?', [$newQty, $existing['id']]);
        } else {
            $this->run(
                'INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES (?, ?, ?, ?)',
                [$cartId, $productId, $sizeId, $qty]
            );
        }
        return null;
    }

    public function setQuantity(int $userId, int $itemId, int $qty): void
    {
        if ($qty < 1) {
            $this->remove($userId, $itemId);
            return;
        }
        $this->run(
            'UPDATE cart_items ci JOIN carts c ON c.id = ci.cart_id SET ci.quantity = ? WHERE ci.id = ? AND c.user_id = ?',
            [min(self::MAX_QTY, $qty), $itemId, $userId]
        );
    }

    public function remove(int $userId, int $itemId): void
    {
        $this->run(
            'DELETE ci FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE ci.id = ? AND c.user_id = ?',
            [$itemId, $userId]
        );
    }

    public function clear(int $userId): void
    {
        $this->run('DELETE ci FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?', [$userId]);
    }
}
