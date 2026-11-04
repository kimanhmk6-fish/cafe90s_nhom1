<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Pagination;
use App\Models\Order;
use App\Models\Payment;

class OrderController extends Controller
{
    private const PER_PAGE = 10;

    private function filters(): array
    {
        $date = fn(string $k) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->input($k)) ? $this->input($k) : '';
        return ['status' => $this->input('status'), 'from' => $date('from'), 'to' => $date('to'), 'q' => $this->input('q')];
    }

    public function index(): void
    {
        $orders = new Order();
        $filters = $this->filters();
        [, $total] = $orders->search($filters, 1, 0);
        $pager = Pagination::fromRequest($total, self::PER_PAGE);
        [$items] = $orders->search($filters, self::PER_PAGE, $pager->offset());
        $this->view('admin/orders/index', ['title' => 'Quản lý đơn hàng', 'items' => $items, 'pager' => $pager, 'filters' => $filters], 'admin');
    }

    public function show(string $id): void
    {
        $orders = new Order();
        $order = $orders->withPayment((int) $id) ?? $this->abort(404);
        $this->view('admin/orders/show', [
            'title' => 'Chi tiết đơn ' . $order['order_code'], 'order' => $order, 'details' => $orders->details((int) $id),
        ], 'admin');
    }

    /** Quy tắc: chỉ chuyển sang Đã xác nhận trở đi khi thanh toán đã được đối soát */
    public function updateStatus(string $id): void
    {
        $this->requireCsrf();
        $orders = new Order();
        $order = $orders->withPayment((int) $id) ?? $this->abort(404);
        $new = $this->input('status');
        $back = "/admin/orders/$id";

        if (!isset(order_statuses()[$new])) {
            flash('error', 'Trạng thái không hợp lệ.');
        } elseif (in_array($order['status'], ['completed', 'cancelled'], true)) {
            flash('error', 'Đơn đã kết thúc, không thể đổi trạng thái.');
        } elseif ($new !== 'cancelled' && $new !== 'pending' && $order['pay_status'] !== 'paid') {
            flash('error', 'Cần xác nhận "Đã thanh toán" (đối soát VietQR) trước khi chuyển sang trạng thái này.');
        } else {
            $orders->setStatus((int) $id, $new);
            flash('success', 'Đã cập nhật trạng thái đơn.');
        }
        redirect($back);
    }

    public function reconcile(string $id): void
    {
        $this->requireCsrf();
        $orders = new Order();
        $order = $orders->findById((int) $id) ?? $this->abort(404);
        $status = $this->input('pay_status');
        $code = mb_substr($this->input('transaction_code'), 0, 100);
        if (!isset(payment_statuses()[$status])) {
            flash('error', 'Trạng thái thanh toán không hợp lệ.');
        } elseif ($status === 'paid' && $code === '') {
            flash('error', 'Vui lòng nhập mã giao dịch ngân hàng để xác nhận đã thanh toán.');
        } elseif ($order['status'] === 'cancelled') {
            flash('error', 'Đơn đã hủy, không thể cập nhật thanh toán.');
        } else {
            (new Payment())->reconcile((int) $id, $status, $code);
            flash('success', 'Đã cập nhật thanh toán.');
        }
        redirect("/admin/orders/$id");
    }

    public function export(): void
    {
        $rows = (new Order())->exportRows($this->filters());
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="don-hang-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng tiếng Việt
        fputcsv($out, ['Mã đơn', 'Khách hàng', 'SĐT', 'Địa chỉ', 'Tạm tính', 'Phí ship', 'Tổng tiền', 'Thanh toán', 'Trạng thái', 'Thời gian đặt']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['order_code'], $r['customer_name'], "'" . $r['customer_phone'], $r['shipping_address'], $r['subtotal'],
                $r['shipping_fee'], $r['total_amount'], payment_statuses()[$r['pay_status']] ?? '',
                order_statuses()[$r['status']], date_vn($r['created_at']),
            ]);
        }
        fclose($out);
    }
}
