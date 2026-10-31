<section class="section auth-section">
  <div class="container auth-wrap">
    <h1>Tạo tài khoản</h1>
    <form class="stack" method="post" action="<?= url('/dang-ky') ?>">
      <?= csrf_field() ?>
      <label>Họ và tên <input name="full_name" required maxlength="100" autocomplete="name" value="<?= e(old('full_name')) ?>"></label>
      <label>Email <input type="email" name="email" required maxlength="150" autocomplete="email" value="<?= e(old('email')) ?>"></label>
      <label>Số điện thoại <input name="phone" required inputmode="tel" pattern="0[0-9 ]{9,12}" autocomplete="tel" value="<?= e(old('phone')) ?>"></label>
      <label>Mật khẩu <span class="hint">(tối thiểu 8 ký tự)</span>
        <span class="pw-field"><input type="password" name="password" required minlength="8" autocomplete="new-password">
          <button type="button" class="pw-toggle" data-pw aria-label="Hiện mật khẩu">Hiện</button></span>
      </label>
      <label>Nhập lại mật khẩu <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password"></label>
      <button class="btn btn-dark btn-block" type="submit">Đăng ký</button>
    </form>
    <p class="auth-switch">Đã có tài khoản? <a href="<?= url('/dang-nhap') ?>">Đăng nhập</a></p>
  </div>
</section>
