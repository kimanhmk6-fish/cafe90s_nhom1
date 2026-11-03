<?php
$qs = fn(array $o) => url('/admin/products') . ($o ? '?' . http_build_query($o) : '');
$keep = array_filter(['q' => $filters['q'], 'status' => $filters['status']]);
$ico = fn(string $d) => '<span class="stat-ico"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg></span>';
?>
<header class="page-head row-between">
  <h1>Quản lý Sản phẩm</h1>
  <a class="btn btn-dark btn-lg" href="<?= url('/admin/products/create') ?>">+ Thêm sản phẩm</a>
</header>

<div class="stat-grid three">
  <div class="stat"><?= $ico('<path d="M4 8l8-4 8 4v8l-8 4-8-4zM4 8l8 4 8-4M12 12v8"/>') ?><span class="stat-num"><?= (int) $stats['total'] ?> sản phẩm</span><span class="stat-sub">Tổng sản phẩm</span></div>
  <div class="stat"><?= $ico('<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/>') ?><span class="stat-num"><?= (int) $stats['featured'] ?> món nổi bật</span><span class="stat-sub">Đang được bật nổi bật</span></div>
  <div class="stat"><?= $ico('<path d="M12 3 3 8l9 5 9-5zM3 13l9 5 9-5"/>') ?><span class="stat-num"><?= count($categories) ?> danh mục</span><span class="stat-sub">Nhóm danh mục đang hoạt động</span></div>
</div>

<form class="toolbar" method="get" role="search">
  <label class="search-box"><span class="sr-only">Tìm theo tên món</span>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
    <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Tìm theo tên món…"></label>
  <?php if ($filters['status']): ?><input type="hidden" name="status" value="<?= e($filters['status']) ?>"><?php endif; ?>
  <a class="chip-link <?= $filters['category'] === '' ? 'is-active' : '' ?>" href="<?= e($qs($keep)) ?>">Tất cả</a>
  <?php foreach ($categories as $c): ?>
    <a class="chip-link <?= $filters['category'] === $c['slug'] ? 'is-active' : '' ?>" href="<?= e($qs($keep + ['category' => $c['slug']])) ?>"><?= e($c['name']) ?></a>
  <?php endforeach; ?>
  <a class="chip-link <?= $filters['status'] === 'hidden' ? 'is-active' : '' ?>" href="<?= e($qs(array_filter(['q' => $filters['q'], 'category' => $filters['category'], 'status' => $filters['status'] === 'hidden' ? '' : 'hidden']))) ?>">Đã ẩn</a>
</form>

<div class="panel table-wrap">
  <table class="table">
    <thead><tr><th scope="col"><span class="sr-only">STT</span></th><th scope="col">Ảnh</th><th scope="col">Tên món</th><th scope="col">Danh mục</th><th scope="col" class="num">Giá gốc</th><th scope="col">Size</th><th scope="col">Nổi bật</th><th scope="col">Trạng thái</th><th scope="col">Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($items as $n => $p): ?>
      <tr>
        <td class="row-no"><?= $pager->offset() + $n + 1 ?></td>
        <td><img class="thumb" src="<?= e(img($p['image'])) ?>" alt="" width="62" height="52"></td>
        <td class="cell-name"><?= e($p['name']) ?></td>
        <td><span class="chip"><?= e($p['category_name']) ?></span></td>
        <td class="num"><?= money($p['base_price']) ?></td>
        <td><?= $p['size_count'] ? 'S · M · L' : '<span class="muted">Không size</span>' ?></td>
        <td>
          <form method="post" action="<?= url('/admin/products/' . (int) $p['id'] . '/toggle') ?>"><?= csrf_field() ?><input type="hidden" name="field" value="featured">
            <button class="switch <?= $p['is_featured'] ? 'on' : '' ?>" type="submit" role="switch" aria-checked="<?= $p['is_featured'] ? 'true' : 'false' ?>" aria-label="Nổi bật: <?= e($p['name']) ?>"></button></form>
        </td>
        <td><span class="badge <?= $p['is_active'] ? 'badge-completed' : 'badge-cancelled' ?>"><?= $p['is_active'] ? 'Đang bán' : 'Đã ẩn' ?></span></td>
        <td class="actions">
          <a href="<?= url('/admin/products/' . (int) $p['id'] . '/edit') ?>">Sửa</a><span class="sep">·</span>
          <form method="post" action="<?= url('/admin/products/' . (int) $p['id'] . '/toggle') ?>"><?= csrf_field() ?><input type="hidden" name="field" value="active">
            <button class="link-plain" type="submit"><?= $p['is_active'] ? 'Ẩn' : 'Hiện' ?></button></form><span class="sep">·</span>
          <form method="post" action="<?= url('/admin/products/' . (int) $p['id'] . '/delete') ?>" data-confirm="Xóa “<?= e($p['name']) ?>”? Nếu món đã có trong đơn hàng, món sẽ chỉ bị ẩn."><?= csrf_field() ?>
            <button class="link-danger" type="submit">Xóa</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?><tr><td colspan="9" class="empty-cell">Không có sản phẩm phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
  <div class="table-foot"><span>Hiển thị <?= count($items) ?> / <?= (int) $pager->total ?> món (tối đa 9 món / trang)</span><?php require BASE_PATH . '/app/Views/partials/pagination.php'; ?></div>
</div>
