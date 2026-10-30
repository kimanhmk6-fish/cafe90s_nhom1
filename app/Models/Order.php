<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class Order extends BaseModel
{
    protected string $table = 'orders';
    protected array $fillable = ['user_id', 'order_code', 'customer_name', 'customer_phone', 'shipping_address', 'note',
        'subtotal', 'shipping_fee', 'total_amount', 'status'];

    /**
     * Tạo đơn từ giỏ hàng bằng transaction: orders + order_details (snapshot giá) + payments, rồi xóa giỏ.
     * @return int id đơn hàng, hoặc 0 nếu giỏ rỗng
     */
    public function createFromCart(int $userId, array $info): int
    {
        $cart = new Cart();
        $items = array_filter($cart->items($userId), fn($i) => $i['is_active']);
        if (!$items) {
            return 0;
        }
        $totals = $cart->totals($items);

        $this->db->beginTransaction();
        try {
            $orderId = $this->create([
                'user_id'          => $userId,
                'order_code'       => $this->nextCode(),
                'customer_name'    => $info['name'],
                'customer_phone'   => $info['phone'],
                'shipping_address' => $info['address'],
                'note'             => $info['note'] ?: null,
                'subtotal'         => $totals['subtotal'],
                'shipping_fee'     => $totals['shipping'],
                'total_amount'     => $totals['total'],
            ]);
            foreach ($items as $i) {
                $this->run(
                    'INSERT INTO order_details (order_id, product_id, size_id, product_name, size_name, price, quantity, total)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [$orderId, $i['product_id'], $i['size_id'], $i['name'], $i['size_name'], $i['price'],
                     $i['quantity'], $i['price'] * $i['quantity']]
                );
            }
            (new Payment())->create(['order_id' => $orderId, 'amount' => $totals['total']]);
            $cart->clear($userId);
            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Mã đơn dạng GCP-20261022-001 */
    private function nextCode(): string
    {
        $prefix = 'GCP-' . date('Ymd') . '-';
        $last = $this->fetchValue('SELECT order_code FROM orders WHERE order_code LIKE ? ORDER BY order_code DESC LIMIT 1 FOR UPDATE', [$prefix . '%']);
        $n = $last ? ((int) substr((string) $last, -3)) + 1 : 1;
        return $prefix . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }

    public function details(int $orderId): array
    {
        return $this->fetchAll('SELECT * FROM order_details WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    /** Tìm đơn theo mã, chỉ trả về nếu thuộc về user (hoặc user là admin) */
    public function findByCodeFor(string $code, int $userId): ?array
    {
        $row = $this->fetchOne('SELECT * FROM orders WHERE order_code = ?', [$code]);
        return $row && ((int) $row['user_id'] === $userId || is_admin()) ? $row : null;
    }

    public function withPayment(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT o.*, p.status AS pay_status, p.payment_method, p.transaction_code, p.paid_at, p.amount AS pay_amount
             FROM orders o LEFT JOIN payments p ON p.order_id = o.id WHERE o.id = ?',
            [$id]
        );
    }

    public function forUser(int $userId): array
    {
        $orders = $this->fetchAll(
            'SELECT o.*, p.status AS pay_status FROM orders o LEFT JOIN payments p ON p.order_id = o.id
             WHERE o.user_id = ? ORDER BY o.created_at DESC, o.id DESC',
            [$userId]
        );
        foreach ($orders as &$o) {
            $o['items'] = $this->details((int) $o['id']);
        }
        return $orders;
    }

    public function setStatus(int $id, string $status): void
    {
        $this->update($id, ['status' => $status]);
    }

    /** Khách tự hủy khi đơn còn "Chờ xác nhận" */
    public function cancelByUser(int $id, int $userId): bool
    {
        return $this->run(
            "UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'",
            [$id, $userId]
        ) > 0;
    }

    private function filterSql(array $f): array
    {
        $where = [];
        $params = [];
        if (!empty($f['status']) && isset(order_statuses()[$f['status']])) {
            $where[] = 'o.status = ?';
            $params[] = $f['status'];
        }
        if (!empty($f['from'])) {
            $where[] = 'o.created_at >= ?';
            $params[] = $f['from'] . ' 00:00:00';
        }
        if (!empty($f['to'])) {
            $where[] = 'o.created_at <= ?';
            $params[] = $f['to'] . ' 23:59:59';
        }
        if (!empty($f['q'])) {
            $where[] = '(o.order_code LIKE ? OR o.customer_phone LIKE ? OR o.customer_name LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private const LIST_SQL = 'SELECT o.*, p.status AS pay_status FROM orders o LEFT JOIN payments p ON p.order_id = o.id';

    public function search(array $filters, int $limit, int $offset): array
    {
        [$sql, $params] = $this->filterSql($filters);
        $total = (int) $this->fetchValue('SELECT COUNT(*) FROM orders o' . $sql, $params);
        $rows = $this->fetchAll(self::LIST_SQL . $sql . " ORDER BY o.created_at DESC, o.id DESC LIMIT $limit OFFSET $offset", $params);
        return [$rows, $total];
    }

    /** Dữ liệu xuất CSV (không phân trang) */
    public function exportRows(array $filters): array
    {
        [$sql, $params] = $this->filterSql($filters);
        return $this->fetchAll(self::LIST_SQL . $sql . ' ORDER BY o.created_at DESC, o.id DESC', $params);
    }

    // ---------- Thống kê Dashboard ----------
    public function statusCounts(): array
    {
        $rows = $this->fetchAll('SELECT status, COUNT(*) AS n FROM orders GROUP BY status');
        $out = array_fill_keys(array_keys(order_statuses()), 0);
        foreach ($rows as $r) {
            $out[$r['status']] = (int) $r['n'];
        }
        return $out;
    }

    /** Doanh thu = đơn đã hoàn thành và đã thanh toán */
    public function revenueTotal(): int
    {
        return (int) $this->fetchValue(
            "SELECT COALESCE(SUM(o.total_amount), 0) FROM orders o JOIN payments p ON p.order_id = o.id
             WHERE o.status = 'completed' AND p.status = 'paid'"
        );
    }

    public function revenueLast7Days(): array
    {
        $rows = $this->fetchAll(
            "SELECT DATE(o.created_at) AS d, SUM(o.total_amount) AS total FROM orders o JOIN payments p ON p.order_id = o.id
             WHERE o.status = 'completed' AND p.status = 'paid' AND o.created_at >= (CURDATE() - INTERVAL 6 DAY)
             GROUP BY DATE(o.created_at)"
        );
        $map = array_column($rows, 'total', 'd');
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $days[] = ['label' => ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][(int) date('w', strtotime($d))], 'total' => (int) ($map[$d] ?? 0)];
        }
        return $days;
    }

    public function latest(int $limit = 5): array
    {
        return $this->fetchAll(self::LIST_SQL . " ORDER BY o.created_at DESC, o.id DESC LIMIT $limit");
    }
}
