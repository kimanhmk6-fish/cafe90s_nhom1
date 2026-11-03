<?php
$ico = [
 'info' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>',
 'orders' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
 'password' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>',
];
$tabs = ['info' => ['/ho-so', 'Thông tin cá nhân'], 'orders' => ['/ho-so/don-hang', 'Lịch sử đơn hàng'], 'password' => ['/ho-so/doi-mat-khau', 'Đổi mật khẩu']];
$tabNow = $tab === 'password' ? 'password' : $tab;
?>
<section class="section page-head-section profile-section">
  <div class="container">
    <h1 class="page-title"><?= $tab === 'orders' ? 'Lịch sử đơn hàng<br>của tôi' : 'Hồ sơ của tôi' ?></h1>
    <div class="profile-layout">
      <aside class="profile-side">
        <img class="avatar-lg" src="<?= e(avatar_url($profile['avatar'] ?? null)) ?>" alt="Ảnh đại diện của <?= e($profile['full_name'] ?? '') ?>" width="126" height="126">
        <form method="post" action="<?= url('/ho-so/avatar') ?>" enctype="multipart/form-data" class="avatar-form">
          <?= csrf_field() ?>
          <label class="btn btn-dark btn-sm">Đổi ảnh đại diện
            <input class="sr-only" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" data-autosubmit-file>
          </label>
          <p class="small" style="margin:0">Chấp nhận jpg, png, webp — tối đa 2MB</p>
        </form>
        <nav class="side-nav" aria-label="Tài khoản">
          <?php foreach ($tabs as $k => [$href, $label]): ?>
            <a href="<?= url($href) ?>" class="<?= $tabNow === $k ? 'is-active' : '' ?>" <?= $tabNow === $k ? 'aria-current="page"' : '' ?>><?= $ico[$k] ?><?= e($label) ?></a>
          <?php endforeach; ?>
          <form method="post" action="<?= url('/dang-xuat') ?>"><?= csrf_field() ?><button type="submit" class="side-logout"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 4H5v16h5M15 8l4 4-4 4M19 12H9"/></svg>Đăng xuất</button></form>
        </nav>
      </aside>

      <div class="profile-main">
      <?php if ($tab !== 'orders'): ?>
        <form class="profile-form" method="post" action="<?= url('/ho-so') ?>">
          <?= csrf_field() ?>
          <h2>Thông tin cá nhân</h2>
          <label>Họ và tên <input name="full_name" required maxlength="100" value="<?= e($profile['full_name'] ?? '') ?>"></label>
          <label>Email <input value="<?= e($account['email']) ?> (không thay đổi)" disabled></label>
          <label>Số điện thoại <input name="phone" required inputmode="tel" pattern="0[0-9]{9,10}" value="<?= e($profile['phone'] ?? '') ?>"></label>
          <label>Địa chỉ giao hàng mặc định <input name="address" maxlength="500" value="<?= e($profile['address'] ?? '') ?>"></label>
          <button class="btn btn-dark btn-block" type="submit">Lưu thay đổi</button>
        </form>
        <hr>
        <form class="profile-form" id="doi-mat-khau" method="post" action="<?= url('/ho-so/doi-mat-khau') ?>">
          <?= csrf_field() ?>
          <h2>Đổi mật khẩu</h2>
          <label>Mật khẩu hiện tại <input type="password" name="current_password" required autocomplete="current-password"></label>
          <label>Mật khẩu mới <input type="password" name="new_password" required minlength="8" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự"></label>
          <label>Nhập lại mật khẩu mới <input type="password" name="new_password_confirm" required minlength="8" autocomplete="new-password"></label>
          <button class="btn btn-outline btn-block" type="submit" style="border-radius:12px">Đổi mật khẩu</button>
        </form>

      <?php else: ?>
        <?php if (!$orders): ?>
          <div class="empty"><p>Bạn chưa có đơn hàng nào.</p><a class="btn btn-dark" href="<?= url('/menu') ?>">Đặt món đầu tiên</a></div>
        <?php endif; ?>
        <?php foreach ($orders as $n => $o): ?>
          <?php if ($n === 0): ?>
          <article class="order-big">
            <header class="row-between">
              <h2><?= e($o['order_code']) ?> · <?= date_vn($o['created_at']) ?></h2>
              <?= status_badge($o['status']) ?>
            </header>
            <table class="table-lines">
              <caption class="sr-only">Món trong đơn <?= e($o['order_code']) ?></caption>
              <tbody>
              <?php foreach ($o['items'] as $d): ?>
                <tr><td><?= e($d['product_name']) ?></td><td><?= $d['size_name'] ? 'Size ' . e($d['size_name']) : '' ?></td><td class="num"><?= money($d['price']) ?> × <?= (int) $d['quantity'] ?></td><td class="num"><?= money($d['total']) ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <p style="margin:0">Tạm tính <?= money($o['subtotal']) ?> · Phí ship <?= money($o['shipping_fee']) ?> · <strong>Tổng cộng <?= money($o['total_amount']) ?></strong> · <?= pay_badge($o['pay_status'] ?? 'pending') ?></p>
            <?php $status = $o['status']; $variant = 'sm'; require BASE_PATH . '/app/Views/partials/order_timeline.php'; ?>
            <div class="row-actions">
              <a class="btn-ghost" href="<?= url('/dat-hang/thanh-cong/' . $o['order_code']) ?>">Xem chi tiết<?= $o['status'] === 'pending' && ($o['pay_status'] ?? '') !== 'paid' ? ' / mã QR' : '' ?></a>
              <?php if ($o['status'] === 'pending'): ?>
                <form method="post" action="<?= url('/ho-so/don-hang/' . (int) $o['id'] . '/huy') ?>" data-confirm="Hủy đơn <?= e($o['order_code']) ?>?"><?= csrf_field() ?>
                  <button class="btn-ghost danger" type="submit">Hủy đơn</button></form>
              <?php endif; ?>
            </div>
          </article>
          <?php else: ?>
          <div class="order-line">
            <p><?= e($o['order_code']) ?> · <?= date_vn($o['created_at'], 'd/m/Y') ?> · Tổng <?= money($o['total_amount']) ?> ·<br><?= e(order_statuses()[$o['status']]) ?></p>
            <?php if ($o['status'] === 'cancelled'): ?><span class="btn-ghost danger">Đã hủy</span>
            <?php else: ?><a class="btn-ghost" href="<?= url('/dat-hang/thanh-cong/' . $o['order_code']) ?>">Xem chi tiết</a><?php endif; ?>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
      </div>
    </div>
  </div>
  <img class="profile-wave" src="<?= asset('images/deco-wave.svg') ?>" alt="" aria-hidden="true">
</section>
