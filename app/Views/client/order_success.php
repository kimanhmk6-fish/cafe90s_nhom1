<section class="section page-head-section">
  <div class="container narrow-wide">
    <div class="success-head">
      <span class="success-icon"><svg width="46" height="46" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11 25 9 9 17-19"/></svg></span>
      <h1>Đặt hàng thành công!</h1>
      <p>Mã đơn <strong><?= e($order['order_code']) ?></strong> · Cảm ơn bạn đã ghé Góc Cà Phê 90s</p>
    </div>

    <div class="success-grid">
      <section class="panel-soft center qr-panel" aria-labelledby="qr-title">
        <h2 id="qr-title"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"/></svg>Thanh toán VietQR</h2>
        <?php if ($order['pay_status'] === 'paid'): ?>
          <p class="paid-note">Đã nhận thanh toán. Cảm ơn bạn!</p>
        <?php elseif ($order['status'] === 'cancelled'): ?>
          <p class="muted">Đơn đã hủy, không cần thanh toán.</p>
        <?php else: ?>
          <img class="qr" src="<?= e($qrUrl) ?>" alt="Mã QR chuyển khoản <?= e(money($order['total_amount'])) ?>" width="190" height="190">
          <p class="bank"><span class="big">Số tiền <?= money($order['total_amount']) ?></span>
            <span>Nội dung chuyển khoản: <strong><?= e($order['order_code']) ?></strong></span>
            <span class="muted small"><?= e(env('BANK_ID', 'MB')) ?> · <?= e(env('BANK_ACCOUNT', '0123456789')) ?></span></p>
          <p class="note-box">Quét mã bằng app ngân hàng.<br>Quán xác nhận thủ công sau khi tiền về.</p>
        <?php endif; ?>
      </section>

      <section class="panel-soft center" aria-labelledby="track-title">
        <h2 id="track-title"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Theo dõi trạng thái đơn</h2>
        <div class="track-wrap">
          <?php $status = $order['status']; $variant = 'lg'; require BASE_PATH . '/app/Views/partials/order_timeline.php'; ?>
          <table class="table table-compact">
            <caption class="sr-only">Chi tiết đơn hàng</caption>
            <tbody>
            <?php foreach ($details as $d): ?>
              <tr><td><?= e($d['product_name']) ?><?= $d['size_name'] ? ' · Size ' . e($d['size_name']) : '' ?> × <?= (int) $d['quantity'] ?></td><td class="num"><?= money($d['total']) ?></td></tr>
            <?php endforeach; ?>
              <tr><td class="muted">Phí vận chuyển</td><td class="num"><?= money($order['shipping_fee']) ?></td></tr>
              <tr class="row-total"><td>Tổng cộng</td><td class="num"><?= money($order['total_amount']) ?></td></tr>
            </tbody>
          </table>
          <a class="btn btn-outline" href="<?= url('/ho-so/don-hang') ?>">Xem lịch sử đơn hàng</a>
        </div>
      </section>
    </div>
  </div>
</section>
