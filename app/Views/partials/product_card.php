<?php
/** @var array $p */
$payload = [
    'id' => (int) $p['id'], 'name' => $p['name'], 'description' => $p['description'], 'category' => $p['category_name'],
    'image' => img($p['image']), 'price' => (int) $p['base_price'], 'sizes' => $p['sizes'],
];
?>
<article class="card product-card" data-product="<?= json_attr($payload) ?>">
  <?php if ($p['is_featured']): ?><span class="tag">Nổi bật</span><?php endif; ?>
  <button type="button" class="card-open" aria-label="Xem chi tiết <?= e($p['name']) ?>">
    <img src="<?= e(img($p['image'])) ?>" alt="<?= e($p['name']) ?>" width="160" height="170" loading="lazy">
    <h3><?= e($p['name']) ?></h3>
    <p class="muted clamp"><?= e($p['description']) ?></p>
  </button>
  <div class="card-foot">
    <strong class="price"><?= $p['size_count'] ? 'từ ' : '' ?><?= money($p['min_price']) ?></strong>
    <button type="button" class="round-add" data-quick-add aria-label="Chọn <?= e($p['name']) ?>">+</button>
  </div>
</article>
