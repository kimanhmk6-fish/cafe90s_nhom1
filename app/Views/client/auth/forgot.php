<section class="section auth-section">
  <div class="container auth-wrap">
    <h1>Quên mật khẩu</h1>
    <p class="muted">Nhập email đã đăng ký, hệ thống sẽ tạo liên kết đặt lại mật khẩu (hiệu lực 30 phút).</p>
    <form class="stack" method="post" action="<?= url('/quen-mat-khau') ?>">
      <?= csrf_field() ?>
      <label>Email <input type="email" name="email" required autocomplete="email"></label>
      <button class="btn btn-dark btn-block" type="submit">Tạo liên kết đặt lại</button>
    </form>
    <?php if (!empty($_SESSION['reset_link'])): ?>
      <div class="note-box">
        <strong>Môi trường học tập chưa cấu hình gửi email.</strong>
        Bấm vào liên kết dưới đây để đặt lại mật khẩu:<br>
        <a href="<?= e($_SESSION['reset_link']) ?>">Đặt lại mật khẩu</a>
      </div>
    <?php endif; ?>
    <p class="auth-switch"><a href="<?= url('/dang-nhap') ?>">Quay lại đăng nhập</a></p>
  </div>
</section>
