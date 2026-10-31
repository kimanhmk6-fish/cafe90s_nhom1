<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\User;

class CartController extends Controller
{
    public function index(): void
    {
        $uid = auth_user()['id'];
        $cart = new Cart();
        $items = $cart->items($uid);
        $this->view('client/cart', [
            'title'   => 'Giỏ hàng & Đặt hàng',
            'items'   => $items,
            'totals'  => $cart->totals($items),
            'profile' => (new User())->profileOf($uid) ?? [],
        ]);
    }

    public function add(): void
    {
        $this->requireCsrf(true);
        if (!auth_user()) {
            $this->json(['ok' => false, 'login' => url('dang-nhap'), 'message' => 'Vui lòng đăng nhập để thêm món vào giỏ.'], 401);
        }
        $cart = new Cart();
        $error = $cart->add(
            auth_user()['id'],
            (int) ($_POST['product_id'] ?? 0),
            isset($_POST['size_id']) && $_POST['size_id'] !== '' ? (int) $_POST['size_id'] : null,
            (int) ($_POST['quantity'] ?? 1)
        );
        if ($error) {
            $this->json(['ok' => false, 'message' => $error], 422);
        }
        $this->json(['ok' => true, 'message' => 'Đã thêm vào giỏ hàng.', 'count' => $cart->count(auth_user()['id'])]);
    }

    public function update(): void
    {
        $this->requireCsrf(true);
        $uid = auth_user()['id'];
        $cart = new Cart();
        $cart->setQuantity($uid, (int) ($_POST['item_id'] ?? 0), (int) ($_POST['quantity'] ?? 1));
        $this->summary($cart, $uid);
    }

    public function remove(): void
    {
        $this->requireCsrf(true);
        $uid = auth_user()['id'];
        $cart = new Cart();
        $cart->remove($uid, (int) ($_POST['item_id'] ?? 0));
        $this->summary($cart, $uid);
    }

    private function summary(Cart $cart, int $uid): never
    {
        $items = $cart->items($uid);
        $lines = [];
        foreach ($items as $i) {
            $lines[$i['id']] = ['quantity' => (int) $i['quantity'], 'total' => money($i['price'] * $i['quantity'])];
        }
        $t = $cart->totals($items);
        $this->json([
            'ok' => true, 'count' => $t['count'], 'empty' => !$items, 'lines' => $lines,
            'subtotal' => money($t['subtotal']), 'shipping' => money($t['shipping']), 'total' => money($t['total']),
        ]);
    }
}
