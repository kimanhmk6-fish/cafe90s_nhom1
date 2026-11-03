<?php
declare(strict_types=1);

namespace App\Controllers\Client;

use App\Core\Controller;
use App\Helpers\UploadHelper;
use App\Models\Order;
use App\Models\User;

class ProfileController extends Controller
{
    public function show(string $tab = 'info'): void
    {
        $uid = auth_user()['id'];
        $users = new User();
        $data = [
            'title'   => 'Hồ sơ của tôi',
            'tab'     => $tab,
            'account' => $users->findById($uid),
            'profile' => $users->profileOf($uid),
        ];
        if ($tab === 'orders') {
            $data['title'] = 'Lịch sử đơn hàng';
            $data['orders'] = (new Order())->forUser($uid);
        }
        $this->view('client/account/profile', $data);
    }

    public function showInfo(): void { $this->show('info'); }
    public function showOrders(): void { $this->show('orders'); }
    public function showPassword(): void { $this->show('password'); }

    public function update(): void
    {
        $this->requireCsrf();
        $name = $this->input('full_name');
        $phone = preg_replace('/\s+/', '', $this->input('phone'));
        $address = $this->input('address');
        $errors = [];
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Họ tên phải từ 2 đến 100 ký tự.';
        if (!preg_match('/^0\d{9,10}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ.';
        if (mb_strlen($address) > 500) $errors[] = 'Địa chỉ quá dài.';
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/ho-so');
        }
        (new User())->updateProfile(auth_user()['id'], ['full_name' => $name, 'phone' => $phone, 'address' => $address]);
        $_SESSION['user']['name'] = $name;
        flash('success', 'Đã cập nhật thông tin cá nhân.');
        redirect('/ho-so');
    }

    public function avatar(): void
    {
        $this->requireCsrf();
        $uid = auth_user()['id'];
        $res = UploadHelper::image($_FILES['avatar'] ?? [], 'avatars');
        if ($res['error'] || !$res['path']) {
            flash('error', $res['error'] ?? 'Vui lòng chọn một ảnh.');
            $this->back('/ho-so');
        }
        $users = new User();
        UploadHelper::remove($users->profileOf($uid)['avatar'] ?? null);
        $users->updateAvatar($uid, $res['path']);
        $_SESSION['user']['avatar'] = $res['path'];
        flash('success', 'Đã đổi ảnh đại diện.');
        $this->back('/ho-so');
    }

    public function password(): void
    {
        $this->requireCsrf();
        $uid = auth_user()['id'];
        $users = new User();
        $account = $users->findById($uid);
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($_POST['current_password'] ?? '', $account['password'])) {
            flash('error', 'Mật khẩu hiện tại không đúng.');
        } elseif (strlen($new) < 8) {
            flash('error', 'Mật khẩu mới tối thiểu 8 ký tự.');
        } elseif ($new !== ($_POST['new_password_confirm'] ?? '')) {
            flash('error', 'Mật khẩu nhập lại không khớp.');
        } else {
            $users->changePassword($uid, $new);
            flash('success', 'Đã đổi mật khẩu.');
        }
        redirect('/ho-so/doi-mat-khau');
    }
}
