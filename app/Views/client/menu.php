<?php
$qs = fn(array $o) => url('/menu') . ($o ? '?' . http_build_query($o) : '');
$keep = array_filter(['q' => $filters['q'], 'sort' => $filters['sort']]);
?>
<section class="section page-head-section">
  <div class="container">
    <h1 class="page-title">Thực đơn</h1>
    <p class="page-sub"><?= (int) $total ?> món · rang xay &amp; pha mới mỗi ngày</p>

    <form class="filters" id="tim-mon" method="get" action="<?= url('/menu') ?>" role="search">
      <label class="search-box">
        <span class="sr-only">Tìm món theo tên</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Tìm món theo tên…" maxlength="60">
      </label>
      <?php if ($filters['category']): ?><input type="hidden" name="category" value="<?= e($filters['category']) ?>"><?php endif; ?>
      <nav class="chips-inline" aria-label="Lọc theo danh mục" style="display:contents">
        <a class="chip-link <?= $filters['category'] === '' ? 'is-active' : '' ?>" href="<?= e($qs($keep)) ?>">Tất cả</a>
        <?php foreach ($categories as $c): ?>
          <a class="chip-link <?= $filters['category'] === $c['slug'] ? 'is-active' : '' ?>" href="<?= e($qs($keep + ['category' => $c['slug']])) ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </nav>
      <label class="select-box">
        <span class="sr-only">Sắp xếp</span>
        <select name="sort" data-autosubmit>
          <option value="">Nổi bật trước</option>
          <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Giá: thấp đến cao</option>
          <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Giá: cao đến thấp</option>
          <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Tên A–Z</option>
        </select>
      </label>
    </form>

    <?php if ($items): ?>
      <div class="grid grid-4 menu-grid">
        <?php foreach ($items as $p) { require BASE_PATH . '/app/Views/partials/product_card.php'; } ?>
      </div>
      <?php require BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    <?php else: ?>
      <div class="empty">
        <p>Không tìm thấy món phù hợp<?= $filters['q'] ? ' với “' . e($filters['q']) . '”' : '' ?>.</p>
        <a class="btn btn-outline btn-sm" href="<?= url('/menu') ?>">Xem tất cả món</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<img class="menu-wave" src="<?= asset('images/deco-wave.svg') ?>" alt="" aria-hidden="true">
