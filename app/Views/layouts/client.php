<?php
$path = request_path();
$nav = ['/' => 'Trang chủ', '/menu' => 'Thực đơn', '/ve-quan' => 'Về quán'];
$user = auth_user();
$pageTitle = ($title ?? '') ? $title . ' · Góc Cà Phê 90s' : 'Góc Cà Phê 90s';
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="Góc Cà Phê 90s – cà phê phin rang xay nguyên bản, đặt món trực tuyến nhanh chóng.">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
</head>
<body class="site" data-base="<?= e(base_url()) ?>">
<a class="skip-link" href="#main">Bỏ qua điều hướng</a>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= url('/') ?>" aria-label="Góc Cà Phê 90s – về trang chủ">Góc Cà Phê 90s</a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Mở menu"><span></span><span></span><span></span></button>
    <nav id="site-nav" class="site-nav" aria-label="Điều hướng chính">
      <ul>
        <?php foreach ($nav as $href => $label): $active = $href === '/' ? $path === '/' : str_starts_with($path, $href); ?>
          <li><a href="<?= url($href) ?>" <?= $active ? 'class="is-active" aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="nav-actions">
        <?php
          $cartBtn = '<a class="btn btn-sm btn-outline cart-link" href="' . url('/gio-hang') . '"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 4h2l2.4 11h10.2l2-8H6.2"/><circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/></svg><span>Giỏ hàng (<span id="cart-count">' . (int) ($cartCount ?? 0) . '</span>)</span></a>';
        ?>
        <?php if ($user): ?>
          <?= $cartBtn ?>
          <a class="nav-user" href="<?= url('/ho-so') ?>">Xin chào, <?= e($user['name']) ?></a>
          <form method="post" action="<?= url('/dang-xuat') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-dark" type="submit">Đăng xuất</button></form>
        <?php else: ?>
          <a class="icon-link" href="<?= url('/menu#tim-mon') ?>" aria-label="Tìm món"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></a>
          <a class="nav-text" href="<?= url('/dang-ky') ?>">Đăng ký</a>
          <a class="btn btn-sm btn-dark" href="<?= url('/dang-nhap') ?>">Đăng nhập</a>
          <?= $cartBtn ?>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>

<?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>

<main id="main"><?= $content ?></main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <p class="footer-brand">Góc Cà Phê 90s</p>
        <p>Không gian retro ấm cúng,<br>cà phê rang xay nguyên bản.</p>
      </div>
      <div>
        <h2>Liên kết</h2>
        <ul>
          <li><a href="<?= url('/') ?>">Trang chủ</a></li>
          <li><a href="<?= url('/menu') ?>">Thực đơn</a></li>
          <li><a href="<?= url('/ve-quan') ?>">Về quán</a></li>
          <li><a href="<?= url('/gio-hang') ?>">Giỏ hàng</a></li>
        </ul>
      </div>
      <div>
        <h2>Liên hệ</h2>
        <p class="contact-line"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11 11 0 0 0 3.6.6 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.6 3.6a1 1 0 0 1-.25 1z"/></svg>0123 456 789</p>
        <p class="contact-line"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>hello@goccaphe90s.com</p>
      </div>
      <div>
        <h2>Giờ mở cửa</h2>
        <p>Thứ 2 – Chủ nhật</p>
        <p>7:00 – 22:00</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Góc Cà Phê 90s</p>
      <div class="social" aria-label="Mạng xã hội">
        <a href="#" aria-label="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M14 8V6.5c0-.7.3-1 1.1-1H17V2h-2.6C11.5 2 10 3.7 10 6.2V8H8v3.5h2V22h4V11.5h2.8L17 8z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor"/></svg></a>
        <a href="#" aria-label="TikTok"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 3c.3 2.2 1.6 3.7 3.8 3.9v3a7 7 0 0 1-3.7-1.2v6.2a5.9 5.9 0 1 1-5.9-5.9c.3 0 .6 0 .9.1v3.1a2.9 2.9 0 1 0 2 2.7V3z"/></svg></a>
      </div>
    </div>
  </div>
</footer>

<dialog id="product-modal" class="modal" aria-labelledby="pm-name">
  <div class="modal-body">
    <button type="button" class="modal-close" data-close aria-label="Đóng">×</button>
    <img id="pm-image" class="modal-img" src="" alt="" width="340" height="420">
    <div class="modal-info">
      <span class="chip" id="pm-category"></span>
      <h2 id="pm-name"></h2>
      <p id="pm-desc" class="muted"></p>
      <fieldset class="size-picker" id="pm-sizes"><legend class="sr-only">Chọn size</legend></fieldset>
      <div class="qty-row">
        <span>Số lượng</span>
        <div class="stepper-box">
          <button type="button" id="pm-minus" aria-label="Giảm số lượng">−</button>
          <output id="pm-qty">1</output>
          <button type="button" id="pm-plus" aria-label="Tăng số lượng">+</button>
        </div>
      </div>
      <button type="button" class="btn btn-dark btn-wide" id="pm-add">Thêm vào giỏ</button>
      <p class="modal-note" id="pm-note"></p>
    </div>
  </div>
</dialog>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
