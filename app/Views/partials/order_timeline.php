<?php
/** @var string $status  @var string $variant 'lg' | 'sm' */
$variant = $variant ?? 'lg';
if ($status === 'cancelled'): ?>
  <p class="timeline-cancelled"><?= status_badge('cancelled') ?> Đơn hàng này đã được hủy.</p>
<?php else:
  $steps = ['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'preparing' => 'Đang pha chế', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành'];
  $keys = array_keys($steps);
  $current = array_search($status, $keys, true);
  $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>';
  $truck = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M2 6h11v10H2zM13 9h5l3 3v4h-8M6 19a1.5 1.5 0 1 0 0-.01M17 19a1.5 1.5 0 1 0 0-.01"/></svg>';
?>
<ol class="timeline <?= $variant === 'sm' ? 'sm' : '' ?>" aria-label="Trạng thái đơn hàng">
  <?php foreach ($steps as $k => $label): $i = array_search($k, $keys, true); $cls = $i < $current ? 'done' : ($i === $current ? 'now' : ''); ?>
    <li class="<?= $cls ?>" <?= $cls === 'now' ? 'aria-current="step"' : '' ?>>
      <span class="dot"><?php if ($variant === 'sm') { echo $i < $current || $status === 'completed' && $i === $current ? $check : ($k === 'shipping' && $cls === 'now' ? $truck : ($cls === 'now' ? $check : '')); } ?></span>
      <span class="lbl"><?= e($label) ?><?= $variant === 'sm' && $i < $current ? ' ✓' : '' ?></span>
    </li>
  <?php endforeach; ?>
</ol>
<?php endif; ?>
