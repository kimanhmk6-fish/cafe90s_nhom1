<section class="section auth-section">
  <div class="container auth-wrap">
    <h1>Đặt lại mật khẩu</h1>
    <form class="stack" method="post" action="<?= url('/dat-lai-mat-khau') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="email" value="<?= e($email) ?>">
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <label>Mật khẩu mới <span class="hint">(tối thiểu 8 ký tự)</span> <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
      <label>Nhập lại mật khẩu mới <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password"></label>
      <button class="btn btn-dark btn-block" type="submit">Đổi mật khẩu</button>
    </form>
  </div>
</section>
