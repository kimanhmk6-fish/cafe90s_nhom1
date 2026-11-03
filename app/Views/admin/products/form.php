<?php
$isEdit = !empty($product['id']);
$v = fn(string $k, $d = '') => old($k, $product[$k] ?? $d);
$hasSize = $isEdit ? !empty($product['sizes']) : !empty(old('has_size'));
if (!$isEdit && empty($_SESSION['_old_now'])) { $hasSize = true; }
$action = $isEdit ? url('/admin/products/' . (int) $product['id']) : url('/admin/products');
?>
<p class="crumb">Quản lý · <a href="<?= url('/admin/products') ?>">Sản phẩm</a> · <?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></p>
<div class="two-col two-col-wide">
<form class="panel form-grid form-panel" method="post" action="<?= $action ?>" enctype="multipart/form-data" id="product-form" data-extra="<?= json_attr($extra) ?>">
  <?= csrf_field() ?>
  <h2><?= $isEdit ? 'Sửa sản phẩm — ' . e($product['name']) : 'Thêm sản phẩm' ?></h2>
  <label class="span-2">Tên món: <input name="name" required maxlength="150" value="<?= e($v('name')) ?>"></label>
  <label>Slug: <span class="hint">(để trống sẽ tự tạo)</span> <input name="slug" maxlength="170" value="<?= e($v('slug')) ?>"></label>
  <label>Danh mục:
    <select name="category_id" required>
      <option value="">— Chọn danh mục —</option>
      <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $v('category_id') === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select></label>
  <label class="span-2">Mô tả: <textarea name="description" rows="2" maxlength="1000"><?= e($v('description')) ?></textarea></label>
  <div class="span-2 upload">
    <img id="img-preview" src="<?= e(img($product['image'] ?? null)) ?>" alt="Ảnh sản phẩm hiện tại" width="96" height="96">
    <label>Ảnh sản phẩm: <span class="hint">Tải ảnh mới (jpg, png, webp — tối đa 2MB)</span>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" id="img-input"></label>
  </div>
  <label class="span-2">Giá gốc (size S): <input name="base_price" required inputmode="numeric" id="base-price" value="<?= e($v('base_price') !== '' ? (int) $v('base_price') : '') ?>"></label>
  <label class="span-2 toggle-row">Món có size <input class="sw" type="checkbox" role="switch" name="has_size" value="1" id="has-size" <?= $hasSize ? 'checked' : '' ?>></label>
  <div class="span-2 size-table" id="size-table" <?= $hasSize ? '' : 'hidden' ?>>
    <p><strong>Bảng giá theo size</strong></p>
    <ul>
      <?php foreach ($extra as $n => $add): ?><li><span class="size-pill"><?= e($n) ?></span> <output data-size="<?= (int) $add ?>">—</output><?= $add ? ' <span class="muted">(+' . number_format($add, 0, ',', '.') . ')</span>' : '' ?></li><?php endforeach; ?>
    </ul>
    <p class="hint">* Bánh &amp; đồ ăn nhẹ: tắt “Có size”, chỉ nhập một giá cố định.</p>
  </div>
  <label class="toggle-row">Món nổi bật <input class="sw" type="checkbox" role="switch" name="is_featured" value="1" <?= $v('is_featured', 0) ? 'checked' : '' ?>></label>
  <label class="toggle-row">Đang bán <input class="sw" type="checkbox" role="switch" name="is_active" value="1" <?= $v('is_active', 1) ? 'checked' : '' ?>></label>
  <div class="span-2 form-actions">
    <a class="btn btn-outline" href="<?= url('/admin/products') ?>">Hủy</a>
    <button class="btn btn-dark" type="submit">Lưu thay đổi</button>
  </div>
</form>
<section class="panel cat-panel" aria-labelledby="cat-title">
  <h2 id="cat-title">Quản lý Danh mục</h2>
  <ul class="cat-list">
    <?php foreach ($categories as $c): ?>
      <li>
        <div><strong><?= e($c['name']) ?></strong><span class="small"><?= (int) $c['product_count'] ?> món</span></div>
        <div class="actions">
          <form method="post" action="<?= url('/admin/categories/' . (int) $c['id'] . '/toggle') ?>"><?= csrf_field() ?>
            <button class="switch <?= $c['is_active'] ? 'on' : '' ?>" type="submit" role="switch" aria-checked="<?= $c['is_active'] ? 'true' : 'false' ?>" aria-label="Hiển thị danh mục <?= e($c['name']) ?>"></button></form>
          <a class="btn-sq" href="<?= url('/admin/categories?edit=' . (int) $c['id']) ?>">Sửa</a>
          <form method="post" action="<?= url('/admin/categories/' . (int) $c['id'] . '/delete') ?>" data-confirm="Xóa danh mục “<?= e($c['name']) ?>”?"><?= csrf_field() ?><button class="btn-sq" type="submit">Xóa</button></form>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
  <a class="btn-add-wide" href="<?= url('/admin/categories') ?>">+ Thêm danh mục</a>
  <p class="hint-i">Chỉ xóa được danh mục không còn sản phẩm.</p>
</section>
</div>
