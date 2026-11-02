<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;

class OrderController extends Controller
{
    public function place(): void
    {
        $this->requireCsrf();
        $uid = auth_user()['id'];
        $info = [
            'name'    => $this->input('customer_name'),
            'phone'   => preg_replace('/\s+/', '', $this->input('customer_phone')),
            'address' => $this->input('shipping_address'),
            'note'    => $this->input('note'),
        ];
        with_old(['customer_name' => $info['name'], 'customer_phone' => $info['phone'],
                  'shipping_address' => $info['address'], 'note' => $info['note']]);

        $errors = [];
        if (mb_strlen($info['name']) < 2 || mb_strlen($info['name']) > 100) $errors[] = 'Vui lòng nhập họ tên người nhận.';
        if (!preg_match('/^0\d{9,10}$/', $info['phone'])) $errors[] = 'Số điện thoại nhận hàng không hợp lệ.';
        if (mb_strlen($info['address']) < 8 || mb_strlen($info['address']) > 500) $errors[] = 'Vui lòng nhập địa chỉ nhận hàng đầy đủ.';
        if (mb_strlen($info['note']) > 300) $errors[] = 'Ghi chú tối đa 300 ký tự.';
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/gio-hang');
        }

        $orderId = (new Order())->createFromCart($uid, $info);
        if (!$orderId) {
            flash('error', 'Giỏ hàng đang trống hoặc các món đã ngừng bán.');
            redirect('/gio-hang');
        }
        $order = (new Order())->findById($orderId);
        redirect('/dat-hang/thanh-cong/' . $order['order_code']);
    }

    public function success(string $code): void
    {
        $orders = new Order();
        $row = $orders->findByCodeFor($code, auth_user()['id']);
        if (!$row) {
            $this->abort(404);
        }
        $order = $orders->withPayment((int) $row['id']);
        $this->view('client/order_success', [
            'title'   => 'Đặt hàng thành công',
            'order'   => $order,
            'details' => $orders->details((int) $order['id']),
            'qrUrl'   => sprintf(
                'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
                rawurlencode((string) env('BANK_ID', 'MB')), rawurlencode((string) env('BANK_ACCOUNT', '0123456789')),
                (int) $order['total_amount'], rawurlencode($order['order_code']), rawurlencode((string) env('BANK_ACCOUNT_NAME', ''))
            ),
        ]);
    }

    public function cancel(string $id): void
    {
        $this->requireCsrf();
        $ok = (new Order())->cancelByUser((int) $id, auth_user()['id']);
        flash($ok ? 'success' : 'error', $ok ? 'Đã hủy đơn hàng.' : 'Chỉ hủy được đơn đang ở trạng thái "Chờ xác nhận".');
        redirect('/ho-so/don-hang');
    }
}
