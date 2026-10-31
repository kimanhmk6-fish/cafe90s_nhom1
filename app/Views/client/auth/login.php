<section class="section auth-section">
  <div class="container auth-wrap">
    <h1>Đăng nhập</h1>
    <form class="stack" method="post" action="<?= url('/dang-nhap') ?>">
      <?= csrf_field() ?>
      <label>Email
        <input type="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>" autofocus>
      </label>
      <label>Mật khẩu
        <span class="pw-field"><input type="password" name="password" required autocomplete="current-password">
          <button type="button" class="pw-toggle" data-pw aria-label="Hiện mật khẩu">Hiện</button></span>
      </label>
      <div class="row-between">
        <label class="check"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>
        <a href="<?= url('/quen-mat-khau') ?>">Quên mật khẩu?</a>
      </div>
      <button class="btn btn-dark btn-block" type="submit">Đăng nhập</button>
    </form>
    <p class="auth-switch">Chưa có tài khoản? <a href="<?= url('/dang-ky') ?>">Đăng ký ngay</a></p>
  </div>
</section>
