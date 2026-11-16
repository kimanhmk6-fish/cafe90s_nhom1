<header class="page-head"><h1>Hộp thư hỗ trợ</h1>
  <p class="muted"><?= (int) $unread ?> tin chưa đọc · Khách gửi từ chat box trên web — xem xong gọi lại theo số điện thoại khách để lại.</p>
</header>
<section class="panel">
  <?php if (!$messages): ?>
    <p class="muted">Chưa có tin nhắn nào.</p>
  <?php else: ?>
  <ul class="cat-list">
    <?php foreach ($messages as $m): ?>
      <li>
        <div>
          <strong><?= e($m['sender_name']) ?></strong>
          <?php if (!$m['is_read']): ?><strong> · Chưa đọc</strong><?php endif; ?>
          <span class="muted small"><?= e(date('d/m/Y H:i', strtotime((string) $m['created_at']))) ?> · <?= $m['user_email'] ? e($m['user_email']) : 'Khách vãng lai' ?></span>
          <p><?= nl2br(e($m['content'])) ?></p>
          <span class="muted small">SĐT: <a href="tel:<?= e($m['phone']) ?>"><?= e($m['phone']) ?></a></span>
        </div>
        <?php if (!$m['is_read']): ?>
          <div class="actions">
            <form method="post" action="<?= url('/admin/support/' . (int) $m['id'] . '/read') ?>"><?= csrf_field() ?>
              <button class="btn-sq" type="submit">Đánh dấu đã đọc</button></form>
          </div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
