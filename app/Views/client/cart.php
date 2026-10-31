<section class="section page-head-section">
  <div class="container">
    <h1 class="page-title">Giỏ hàng &amp; Đặt hàng</h1>

    <?php if (!$items): ?>
      <div class="empty">
        <p>Giỏ hàng của bạn đang trống.</p>
        <a class="btn btn-dark" href="<?= url('/menu') ?>">Chọn món ngay</a>
      </div>
    <?php else: ?>
    <div class="checkout" id="cart-root" data-update="<?= url('/gio-hang/cap-nhat') ?>" data-remove="<?= url('/gio-hang/xoa') ?>">
      <div class="checkout-main">
        <section class="cart-panel" aria-labelledby="cart-title">
          <h2 id="cart-title">Giỏ hàng (<span id="cart-lines"><?= count($items) ?></span> sản phẩm)</h2>
          <ul class="cart-list">
            <?php foreach ($items as $i): ?>
              <li class="cart-item" data-item="<?= (int) $i['id'] ?>" data-price="<?= (int) $i['price'] ?>">
                <img src="<?= e(img($i['image'])) ?>" alt="" width="64" height="64">
                <div class="cart-name">
                  <strong><?= e($i['name']) ?><?= $i['size_name'] ? ' · Size ' . e($i['size_name']) : '' ?></strong>
                  <span class="unit"><?= money($i['price']) ?></span>
                  <?php if (!$i['is_active']): ?><span class="warn">Món đã ngừng bán – vui lòng xóa khỏi giỏ</span><?php endif; ?>
                </div>
                <div class="stepper" role="group" aria-label="Số lượng <?= e($i['name']) ?>">
                  <button type="button" data-step="-1" aria-label="Giảm">−</button>
                  <output><?= (int) $i['quantity'] ?></output>
                  <button type="button" data-step="1" aria-label="Tăng">+</button>
                </div>
                <span class="line-total"><?= money($i['price'] * $i['quantity']) ?></span>
                <button type="button" class="icon-btn" data-remove aria-label="Xóa <?= e($i['name']) ?> khỏi giỏ">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                </button>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>

        <form id="checkout-form" class="ship-panel" method="post" action="<?= url('/dat-hang') ?>" novalidate>
          <?= csrf_field() ?>
          <h2>Thông tin giao hàng</h2>
          <div class="rows">
            <label class="field-row">Họ tên
              <input name="customer_name" required maxlength="100" autocomplete="name" value="<?= e(old('customer_name', $profile['full_name'] ?? '')) ?>">
            </label>
            <label class="field-row">Số điện thoại
              <input name="customer_phone" required inputmode="tel" pattern="0[0-9]{9,10}" maxlength="11" autocomplete="tel" placeholder="09xx xxx xxx" value="<?= e(old('customer_phone', $profile['phone'] ?? '')) ?>">
            </label>
            <label class="field-row">Địa chỉ nhận hàng
              <input name="shipping_address" required minlength="8" maxlength="500" autocomplete="street-address" value="<?= e(old('shipping_address', $profile['address'] ?? '')) ?>">
            </label>
            <label class="note-row">Ghi chú cho quán (không bắt buộc)
              <input name="note" maxlength="300" placeholder="Ít đá, ít ngọt…" value="<?= e(old('note')) ?>">
            </label>
          </div>
        </form>
      </div>

      <aside class="summary" aria-labelledby="sum-title">
        <h2 id="sum-title">Tóm tắt đơn</h2>
        <dl class="sum-rows">
          <div><dt>Tạm tính</dt><dd id="sum-subtotal"><?= money($totals['subtotal']) ?></dd></div>
          <div><dt>Phí vận chuyển</dt><dd id="sum-shipping"><?= money($totals['shipping']) ?></dd></div>
          <div class="sum-total"><dt>Tổng thanh toán</dt><dd id="sum-total"><?= money($totals['total']) ?></dd></div>
        </dl>
        <button class="btn btn-dark btn-block btn-rect" type="submit" form="checkout-form">Đặt hàng · Thanh toán VietQR</button>
        <p class="small">Thanh toán bằng mã VietQR sau khi đặt hàng.</p>
      </aside>
    </div>
    <?php endif; ?>
  </div>
</section>
<img class="menu-wave" src="<?= asset('images/deco-wave.svg') ?>" alt="" aria-hidden="true">
