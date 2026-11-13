<header class="page-head head-right"><h1>Quản lý Người dùng</h1></header>
<?php $uq = fn(string $f) => url('/admin/users') . '?' . http_build_query(array_filter(['q' => $q, 'filter' => $f])); ?>
<form class="toolbar" method="get" role="search">
  <label class="search-box"><span class="sr-only">Tìm người dùng</span>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm theo email, họ tên hoặc số điện thoại…"></label>
  <?php if ($filter): ?><input type="hidden" name="filter" value="<?= e($filter) ?>"><?php endif; ?>
  <a class="chip-link <?= $filter === '' ? 'is-active' : '' ?>" href="<?= e($uq('')) ?>">Tất cả</a>
  <a class="chip-link <?= $filter === 'customer' ? 'is-active' : '' ?>" href="<?= e($uq('customer')) ?>">Khách hàng</a>
  <a class="chip-link <?= $filter === 'admin' ? 'is-active' : '' ?>" href="<?= e($uq('admin')) ?>">Admin</a>
  <a class="chip-link <?= $filter === 'locked' ? 'is-active' : '' ?>" href="<?= e($uq('locked')) ?>">Đã khóa</a>
</form>
<div class="panel table-wrap">
  <table class="table">
    <thead><tr><th scope="col">Họ tên</th><th scope="col">Email</th><th scope="col">Số điện thoại</th><th scope="col">Vai trò</th><th scope="col">Trạng thái</th><th scope="col">Ngày đăng ký</th><th scope="col">Thao tác</th></tr></thead>
    <tbody>
    <?php foreach ($items as $u): $isAdmin = $u['role'] === 'admin'; ?>
      <tr>
        <td><span class="user-cell"><?php if ($u['avatar']): ?><img class="thumb round" src="<?= e(avatar_url($u['avatar'])) ?>" alt="" width="46" height="46"><?php else: ?><span class="initials"><?= e(initials($u['full_name'] ?? $u['email'])) ?></span><?php endif; ?><?= e($u['full_name'] ?? '—') ?></span></td>
        <td><span class="chip"><?= e($u['email']) ?></span></td>
        <td><?= e($u['phone'] ?? '') ?></td>
        <td><?= $isAdmin ? '<span class="chip">Admin</span>' : '<span class="chip">Khách hàng</span>' ?></td>
        <td><?= $u['is_active'] ? '<span class="badge badge-completed">Hoạt động</span>' : '<span class="badge badge-cancelled">Đã khóa</span>' ?></td>
        <td><?= date_vn($u['created_at'], 'd/m/Y') ?></td>
        <td class="actions">
          <?php if ($isAdmin): ?><span class="muted">Khóa · Hạ quyền</span>
          <?php else: ?>
            <form method="post" action="<?= url('/admin/users/' . (int) $u['id'] . '/toggle') ?>" data-confirm="<?= $u['is_active'] ? 'Khóa' : 'Mở khóa' ?> tài khoản <?= e($u['email']) ?>?"><?= csrf_field() ?>
              <button class="link-danger" type="submit"><?= $u['is_active'] ? 'Khóa' : 'Mở khóa' ?></button></form>
            <form method="post" action="<?= url('/admin/users/' . (int) $u['id'] . '/promote') ?>" data-confirm="Nâng <?= e($u['email']) ?> lên Admin? Thao tác này không thể hoàn tác trong giao diện."><?= csrf_field() ?>
              <button class="link-plain" type="submit">Nâng Admin</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?><tr><td colspan="7" class="empty-cell">Không có người dùng phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
  <div class="table-foot"><span>Không thể khóa hoặc hạ quyền tài khoản của chính mình và của các Admin khác.</span><?php require BASE_PATH . '/app/Views/partials/pagination.php'; ?></div>
</div>
