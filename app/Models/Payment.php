<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class Payment extends BaseModel
{
    protected string $table = 'payments';
    protected array $fillable = ['order_id', 'payment_method', 'amount', 'status', 'transaction_code', 'paid_at'];

    public function findByOrder(int $orderId): ?array
    {
        return $this->fetchOne('SELECT * FROM payments WHERE order_id = ?', [$orderId]);
    }

    /** Admin đối soát thủ công: cập nhật trạng thái + mã giao dịch ngân hàng */
    public function reconcile(int $orderId, string $status, ?string $transactionCode): void
    {
        $paidAt = $status === 'paid' ? date('Y-m-d H:i:s') : null;
        $this->run(
            'UPDATE payments SET status = ?, transaction_code = ?, paid_at = ? WHERE order_id = ?',
            [$status, $transactionCode ?: null, $paidAt, $orderId]
        );
    }
}
