<?php
$path = request_path();
$user = auth_user();
$menu = [
  '/admin' => ['Dashboard', '<path d="M3 3h8v8H3zM13 3h8v5h-8zM13 10h8v11h-8zM3 13h8v8H3z"/>'],
  '/admin/products' => ['Sản phẩm', '<path d="M4 8l8-4 8 4v8l-8 4-8-4zM4 8l8 4 8-4M12 12v8"/>'],
  '/admin/categories' => ['Danh mục', '<path d="M12 3 3 8l9 5 9-5zM3 13l9 5 9-5"/>'],
  '/admin/orders' => ['Đơn hàng', '<path d="M7 4h10v16H7zM10 9h4M10 13h4"/>'],
  '/admin/users' => ['Người dùng', '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.2-5.5 6.5-5.5s5.9 1.9 6.5 5.5M16 4.8a3.5 3.5 0 0 1 0 6.4M18 14.8c2 .6 3.3 2.3 3.5 5"/>'],
];
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Quản trị') ?> · Quản trị Góc Cà Phê 90s</title>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="robots" content="noindex">
<link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin" data-base="<?= e(base_url()) ?>">
<a class="skip-link" href="#main">Bỏ qua điều hướng</a>
<div class="admin-shell">
  <aside class="sidebar" id="sidebar">
    <a class="side-brand" href="<?= url('/admin') ?>"><span class="cup"><svg width="30" height="30" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M9 20h26v9a10 10 0 0 1-10 10h-6A10 10 0 0 1 9 29zM35 22h3a4.5 4.5 0 0 1 0 9h-3M17 8c0 3 3 3 3 6M25 8c0 3 3 3 3 6"/></svg></span>Góc Cà Phê 90s<small><?= e($user['name']) ?> · Quản trị</small></a>
    <nav aria-label="Menu quản trị">
      <ul>
      <?php foreach ($menu as $href => [$label, $icon]):
        $active = $href === '/admin' ? $path === '/admin' : str_starts_with($path, $href); ?>
        <li><a href="<?= url($href) ?>" class="<?= $active ? 'is-active' : '' ?>" <?= $active ? 'aria-current="page"' : '' ?>>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><?= $icon ?></svg><?= e($label) ?></a></li>
      <?php endforeach; ?>
      </ul>
    </nav>
    <div class="side-foot">
      <div class="side-user"><span class="avatar-sm"><?= e(initials($user['name'])) ?></span>
        <span><strong>Admin <?= e($user['name']) ?></strong><small>Quản trị viên</small></span></div>
      <a class="side-link" href="<?= url('/') ?>" target="_blank" rel="noopener"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5H5V6h5"/></svg>Xem website</a>
      <form method="post" action="<?= url('/admin/logout') ?>"><?= csrf_field() ?><button class="side-link" type="submit"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 4H5v16h5M15 8l4 4-4 4M19 12H9"/></svg>Đăng xuất</button></form>
    </div>
  </aside>
  <div class="admin-main">
    <button class="menu-btn" type="button" aria-controls="sidebar" aria-expanded="false" data-sidebar-toggle>☰ Menu</button>
    <main id="main">
      <?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>
      <?= $content ?>
    </main>
  </div>
</div>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
