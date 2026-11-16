<?php
/** Chat box góc phải dưới: tab Hỏi đáp nhanh (bot FAQ) + tab Nhắn cho quán (vào hộp thư admin). */
$cbUser  = auth_user();
$cbName  = $cbUser['name'] ?? '';
$cbPhone = '';
if ($cbUser) {
    $cbProfile = (new \App\Models\User())->profileOf((int) $cbUser['id']);
    $cbName  = $cbProfile['full_name'] ?? $cbName;
    $cbPhone = $cbProfile['phone'] ?? '';
}
?>
<div class="cb" id="chatbox">
  <button type="button" class="cb-toggle" id="cb-toggle" aria-expanded="false" aria-controls="cb-panel">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.2 0-2.4-.25-3.4-.7L3 21l1.7-6.1A8.5 8.5 0 1 1 21 11.5z"/></svg>
    <span>Hỗ trợ</span>
  </button>

  <section class="cb-panel" id="cb-panel" hidden aria-label="Hỗ trợ khách hàng">
    <header class="cb-head">
      <div><strong>Góc Cà Phê 90s</strong><small>Hỏi đáp nhanh hoặc nhắn cho quán</small></div>
      <button type="button" class="cb-close" id="cb-close" aria-label="Đóng chat">×</button>
    </header>

    <div class="cb-tabs" role="tablist" aria-label="Chế độ hỗ trợ">
      <button type="button" class="cb-tab is-active" data-cb-tab="faq" role="tab" aria-selected="true">Hỏi đáp nhanh</button>
      <button type="button" class="cb-tab" data-cb-tab="form" role="tab" aria-selected="false">Nhắn cho quán</button>
    </div>

    <div class="cb-page" data-cb-page="faq">
      <div class="cb-log" id="cb-log" aria-live="polite"></div>
      <div class="cb-chips" id="cb-chips"></div>
      <form class="cb-ask" id="cb-ask">
        <input id="cb-question" type="text" maxlength="200" placeholder="Nhập câu hỏi của bạn..." autocomplete="off" aria-label="Câu hỏi">
        <button type="submit">Gửi</button>
      </form>
    </div>

    <div class="cb-page" data-cb-page="form" hidden>
      <form class="cb-form" id="cb-form" data-url="<?= url('/ho-tro/gui') ?>">
        <label>Họ tên
          <input name="sender_name" required maxlength="100" value="<?= e($cbName) ?>" placeholder="Tên của bạn">
        </label>
        <label>Số điện thoại
          <input name="phone" required inputmode="tel" maxlength="15" value="<?= e($cbPhone) ?>" placeholder="09xx xxx xxx">
        </label>
        <label>Nội dung
          <textarea name="content" rows="4" required maxlength="1000" placeholder="Bạn cần quán hỗ trợ điều gì?"></textarea>
        </label>
        <button type="submit" class="cb-send">Gửi tin nhắn</button>
        <p class="cb-note" id="cb-form-note">Tin nhắn gửi thẳng tới quán, quán sẽ gọi lại theo số điện thoại bạn để lại.</p>
      </form>
    </div>
  </section>
</div>
