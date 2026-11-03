<?php $closed = in_array($order['status'], ['completed', 'cancelled'], true); ?>
<header class="page-head row-between">
  <div><a class="btn btn-outline" href="<?= url('/admin/orders') ?>">← Quay lại danh sách</a></div><h1>Chi tiết đơn <?= e($order['order_code']) ?></h1>
</header>
<div class="two-col two-col-wide">
  <section class="panel">
    <h2>Chi tiết đơn <?= e($order['order_code']) ?></h2>
    <p class="info-box"><strong>Thông tin khách hàng:</strong><br><strong><?= e($order['customer_name']) ?></strong> · <?= e($order['customer_phone']) ?><br><?= e($order['shipping_address']) ?>
      <?php if ($order['note']): ?><br><em>Ghi chú: <?= e($order['note']) ?></em><?php endif; ?><br>
      <span class="small">Đặt lúc: <?= date_vn($order['created_at']) ?></span></p>

    <h3>Trạng thái hiện tại: <?= status_badge($order['status']) ?></h3>
    <?php if (!$closed): ?>
    <form class="inline-form" method="post" action="<?= url('/admin/orders/' . (int) $order['id'] . '/status') ?>">
      <?= csrf_field() ?>
      <label class="sr-only" for="st">Trạng thái mới</label>
      <select id="st" name="status">
        <?php foreach (order_statuses() as $k => $l): ?><option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-dark btn-sm" type="submit">Cập nhật trạng thái</button>
    </form>
    <?php else: ?><p class="muted small">Đơn đã kết thúc, không thể đổi trạng thái.</p><?php endif; ?>

    <h3>Bảng hóa đơn</h3>
    <table class="table table-compact">
      <thead><tr><th scope="col">Món</th><th scope="col">Size</th><th scope="col" class="num">Đơn giá × SL</th><th scope="col" class="num">Thành tiền</th></tr></thead>
      <tbody>
      <?php foreach ($details as $d): ?>
        <tr><td><?= e($d['product_name']) ?></td><td><?= $d['size_name'] ? e($d['size_name']) : '<span class="muted">(không size)</span>' ?></td><td class="num"><?= money($d['price']) ?> × <?= (int) $d['quantity'] ?></td><td class="num"><?= money($d['total']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3">Tạm tính</td><td class="num"><?= money($order['subtotal']) ?></td></tr>
        <tr><td colspan="3">Phí giao hàng</td><td class="num"><?= money($order['shipping_fee']) ?></td></tr>
        <tr class="row-total"><td colspan="3">TỔNG CỘNG</td><td class="num"><?= money($order['total_amount']) ?></td></tr>
      </tfoot>
    </table>
  </section>

  <section class="panel">
    <h2>Đối soát VietQR (thủ công)</h2>
    <dl class="kv">
      <div><dt>Số tiền cần thu</dt><dd><strong><?= money($order['total_amount']) ?></strong></dd></div>
      <div><dt>Nội dung chuyển khoản</dt><dd><code><?= e($order['order_code']) ?></code></dd></div>
      <div><dt>Trạng thái thanh toán</dt><dd><?= pay_badge($order['pay_status'] ?? 'pending') ?></dd></div>
      <?php if ($order['paid_at']): ?><div><dt>Thời gian thanh toán</dt><dd><?= date_vn($order['paid_at']) ?></dd></div><?php endif; ?>
    </dl>
    <?php if ($order['status'] !== 'cancelled'): ?>
    <form class="stack" method="post" action="<?= url('/admin/orders/' . (int) $order['id'] . '/payment') ?>">
      <?= csrf_field() ?>
      <label>Mã giao dịch ngân hàng <input name="transaction_code" maxlength="100" value="<?= e($order['transaction_code']) ?>" placeholder="FT24298xxxxxx"></label>
      <label>Trạng thái thanh toán
        <select name="pay_status"><?php foreach (payment_statuses() as $k => $l): ?><option value="<?= $k ?>" <?= ($order['pay_status'] ?? 'pending') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
      <button class="btn-ok btn" type="submit">✓ Xác nhận đã thanh toán / Lưu đối soát</button>
    </form>
    <?php endif; ?>
    <p class="note-box small">Quy tắc: chỉ chuyển đơn từ “Chờ xác nhận” sang “Đã xác nhận” sau khi thanh toán được xác nhận đã nhận.</p>
  </section>
</div>
