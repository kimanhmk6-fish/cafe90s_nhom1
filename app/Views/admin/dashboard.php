<?php
$max = max(1, ...array_column($days, 'total'));
$colors = ['pending' => '#f2b73c', 'confirmed' => '#4f7fc4', 'preparing' => '#4a2a1b', 'shipping' => '#7fb3d9', 'completed' => '#4f9a55', 'cancelled' => '#9a948d'];
$short = fn(int $n) => $n >= 1000000 ? rtrim(rtrim(number_format($n / 1e6, 1, '.', ''), '0'), '.') . 'M' : ($n > 0 ? round($n / 1000) . 'k' : '0');
$ico = [
  'box' => '<path d="M4 8l8-4 8 4v8l-8 4-8-4zM4 8l8 4 8-4M12 12v8"/>',
  'star' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8z"/>',
  'bill' => '<path d="M7 3h10v18l-2.5-1.5L12 21l-2.5-1.5L7 21zM10 8h4M10 12h4"/>',
  'cash' => '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/>',
];
$icon = fn(string $k) => '<span class="stat-ico"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true">' . $ico[$k] . '</svg></span>';
?>
<header class="page-head head-right"><h1>Dashboard</h1><p>— Tổng quan quán</p></header>

<div class="stat-grid">
  <div class="stat"><?= $icon('box') ?><span class="stat-num"><?= (int) $productCount ?> sản phẩm</span><span class="stat-sub">Đang hoạt động</span></div>
  <div class="stat"><?= $icon('star') ?><span class="stat-num"><?= (int) $customers ?> khách hàng</span><span class="stat-sub">Tổng khách hàng</span></div>
  <div class="stat"><?= $icon('bill') ?><span class="stat-num"><?= (int) $orderCount ?> đơn hàng</span><span class="stat-sub">Tổng đơn trên hệ thống</span></div>
  <div class="stat"><?= $icon('cash') ?><span class="stat-num"><?= money($revenue) ?></span><span class="stat-sub">Doanh thu (đơn hoàn thành &amp; đã thanh toán)</span></div>
</div>

<div class="two-col">
  <section class="panel">
    <h2>Đơn hàng theo trạng thái</h2>
    <ul class="legend">
      <?php foreach (order_statuses() as $k => $label): ?>
        <li><span class="swatch" style="background:<?= $colors[$k] ?>"></span><?= e($label) ?> <strong><?= (int) $statusCounts[$k] ?></strong></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <section class="panel">
    <h2>Doanh thu 7 ngày gần nhất</h2>
    <div class="bars" role="img" aria-label="Biểu đồ doanh thu 7 ngày gần nhất">
      <?php foreach ($days as $d): ?>
        <div class="bar-col">
          <span class="bar" style="height:<?= max(4, round($d['total'] / $max * 100)) ?>%"></span>
          <span class="bar-lbl"><?= e($d['label']) ?></span>
          <span class="bar-val"><?= $short($d['total']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<section class="panel">
  <div class="row-between"><h2>Đơn hàng mới nhất</h2><a href="<?= url('/admin/orders') ?>">Xem tất cả</a></div>
  <?php if (!$latest): ?><p class="muted">Chưa có đơn hàng nào.</p><?php else: ?>
  <ul class="latest">
    <?php foreach ($latest as $o): ?>
      <li><span><a href="<?= url('/admin/orders/' . (int) $o['id']) ?>"><?= e($o['order_code']) ?></a> · <?= e($o['customer_name']) ?> · <?= money($o['total_amount']) ?> · <?= e(order_statuses()[$o['status']]) ?></span><?= status_badge($o['status']) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
