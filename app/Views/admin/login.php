<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập quản trị · Góc Cà Phê 90s</title>
<meta name="robots" content="noindex">
<link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin admin-login">
<main class="login-card">
  <svg width="64" height="64" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 28h34v10a14 14 0 0 1-14 14h-6A14 14 0 0 1 12 38zM46 31h4a6 6 0 0 1 0 12h-4M22 12c0 4 4 4 4 8M32 12c0 4 4 4 4 8"/><path d="M8 54h46"/></svg>
  <h1>Góc Cà Phê 90s</h1>
  <p class="sub">Khu vực quản trị — /admin</p>
  <h2>Đăng nhập quản trị</h2>
  <?php foreach ($flashes as $f): ?><div class="flash flash-<?= e($f['type']) ?>" role="alert"><?= e($f['message']) ?></div><?php endforeach; ?>
  <form class="stack" method="post" action="<?= url('/admin/login') ?>">
    <?= csrf_field() ?>
    <label>Email quản trị: <input type="email" name="email" required autocomplete="username" value="<?= e(old('email')) ?>" autofocus></label>
    <label>Mật khẩu:
      <span class="pw-field"><input type="password" name="password" required autocomplete="current-password">
        <button type="button" class="pw-toggle" data-pw aria-label="Hiện mật khẩu">Hiện</button></span>
    </label>
    <button class="btn btn-dark btn-block" type="submit">Đăng nhập quản trị</button>
  </form>
  <p class="small">Chỉ tài khoản có vai trò Admin và đang hoạt động mới truy cập được.<br>Tài khoản khách hàng sẽ bị chặn ngay tại trang này.</p>
</main>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
