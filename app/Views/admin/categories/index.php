<header class="page-head"><h1>Quản lý Danh mục</h1><p class="muted">Chỉ xóa được danh mục không còn sản phẩm.</p></header>
<div class="two-col two-col-wide">
  <section class="panel">
    <h2>Danh sách danh mục</h2>
    <ul class="cat-list">
      <?php foreach ($categories as $c): ?>
        <li>
          <div><strong><?= e($c['name']) ?></strong><span class="muted small"><?= (int) $c['product_count'] ?> món · /<?= e($c['slug']) ?></span></div>
          <div class="actions">
            <form method="post" action="<?= url('/admin/categories/' . (int) $c['id'] . '/toggle') ?>"><?= csrf_field() ?>
              <button class="switch <?= $c['is_active'] ? 'on' : '' ?>" type="submit" role="switch" aria-checked="<?= $c['is_active'] ? 'true' : 'false' ?>" aria-label="Hiển thị danh mục <?= e($c['name']) ?>"></button></form>
            <a class="btn-sq" href="<?= url('/admin/categories?edit=' . (int) $c['id']) ?>">Sửa</a>
            <form method="post" action="<?= url('/admin/categories/' . (int) $c['id'] . '/delete') ?>" data-confirm="Xóa danh mục “<?= e($c['name']) ?>”?"><?= csrf_field() ?>
              <button class="btn-sq" type="submit">Xóa</button></form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <section class="panel">
    <h2><?= $editing ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>
    <form class="stack" method="post" action="<?= url('/admin/categories' . ($editing ? '/' . (int) $editing['id'] : '')) ?>">
      <?= csrf_field() ?>
      <label>Tên danh mục <input name="name" required maxlength="100" value="<?= e($editing['name'] ?? '') ?>"></label>
      <label>Mô tả <textarea name="description" rows="3" maxlength="500"><?= e($editing['description'] ?? '') ?></textarea></label>
      <div class="form-actions">
        <?php if ($editing): ?><a class="btn btn-outline" href="<?= url('/admin/categories') ?>">Hủy</a><?php endif; ?>
        <button class="btn btn-dark" type="submit"><?= $editing ? 'Lưu' : '+ Thêm danh mục' ?></button>
      </div>
    </form>
  </section>
</div>
