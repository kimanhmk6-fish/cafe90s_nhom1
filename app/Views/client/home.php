<?php
$vibeImg = ['ca-phe' => 'vibe-ca-phe.png', 'tra' => 'vibe-tra.png', 'banh' => 'vibe-banh.png'];
$vibeTxt = ['ca-phe' => 'Đậm vị xưa', 'tra' => 'Thanh mát, ngọt dịu', 'banh' => 'Ăn kèm trọn buổi'];
$arrow = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
?>
<section class="hero">
  <img class="deco deco-sprig" src="<?= asset('images/deco-scribble.svg') ?>" alt="" width="64" style="left:44%;top:56%">
  <div class="container hero-grid">
    <div class="hero-copy">
      <p class="script script-caps">Đừng chỉ<br>uống cà phê.</p>
      <h1>Hãy giữ lại một khoảnh khắc xưa</h1>
      <p class="lead">Hương vị xưa trong từng giọt cà phê — rang xay nguyên bản, pha phin nhỏ giọt như những năm 90.</p>
      <a class="btn btn-dark" href="<?= url('/menu') ?>">Khám phá Thực đơn <?= $arrow ?></a>
      <p class="script scroll-hint">↓ Cuộn xuống khám phá</p>
    </div>
    <figure class="hero-photo">
      <img src="<?= asset('images/hero-phin.jpg') ?>" alt="Phin cà phê đang nhỏ giọt vào ly thủy tinh trên bàn gỗ" width="431" height="437">
    </figure>
    <ul class="hero-picks" aria-label="Món gợi ý">
      <?php foreach (array_slice($featured, 0, 4) as $p): ?>
        <li><button type="button" class="pick" data-product="<?= json_attr(['id' => (int) $p['id'], 'name' => $p['name'], 'description' => $p['description'], 'category' => $p['category_name'], 'image' => img($p['image']), 'price' => (int) $p['base_price'], 'sizes' => $p['sizes']]) ?>">
          <img src="<?= e(img($p['image'])) ?>" alt="" width="76" height="76">
          <span><strong><?= e($p['name']) ?></strong><small><?= $p['size_count'] ? 'từ ' : '' ?><?= money($p['min_price']) ?></small></span>
        </button></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <img class="hero-wave" src="<?= asset('images/deco-wave.svg') ?>" alt="" aria-hidden="true">
</section>

<section class="vibe-section">
  <img class="deco leaves-tl" src="<?= asset('images/deco-leaves.svg') ?>" alt="" aria-hidden="true">
  <img class="deco leaves-br" src="<?= asset('images/deco-leaves.svg') ?>" alt="" aria-hidden="true">
  <div class="container vibe">
    <div class="vibe-copy">
      <p class="eyebrow-caps">Bắt đầu từ đâu?</p>
      <h2>Chọn theo vibe hôm nay</h2>
      <p>Ba danh mục, đủ cho mọi tâm trạng.</p>
      <a class="link-arrow" href="<?= url('/menu') ?>">Xem toàn bộ thực đơn →</a><br>
      <span class="script">chọn vibe trước,<br>chọn món sau</span>
    </div>
    <ul class="vibe-cards">
      <?php foreach ($categories as $c): ?>
        <li>
          <a class="vibe-card" href="<?= url('/menu?category=' . urlencode($c['slug'])) ?>">
            <img src="<?= asset('images/' . ($vibeImg[$c['slug']] ?? 'vibe-ca-phe.png')) ?>" alt="" width="214" height="214" loading="lazy">
            <h3><?= e($c['name']) ?></h3>
            <p><?= e($vibeTxt[$c['slug']] ?? '') ?></p>
            <span class="circle-go" aria-hidden="true"><?= $arrow ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div><p class="eyebrow-spaced">Món nổi bật</p><h2>Best Seller của quán</h2></div>
      <a class="link-arrow" href="<?= url('/menu') ?>">Xem tất cả thực đơn →</a>
    </div>
    <?php if ($featured): ?>
      <div class="grid grid-3">
        <?php foreach ($featured as $p) { require BASE_PATH . '/app/Views/partials/product_card.php'; } ?>
      </div>
    <?php else: ?>
      <p class="empty">Món nổi bật sẽ sớm được cập nhật.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container about-teaser">
    <img src="<?= asset('images/quan-90s-wide.jpg') ?>" alt="Góc quán với tường gạch, bàn gỗ, ghế mây và đèn vàng ấm" width="471" height="300" loading="lazy">
    <div class="about-text">
      <p class="label">Về quán</p>
      <h2>Một góc nhỏ của những năm 90</h2>
      <p><strong>Không gian retro ấm cúng với bàn gỗ, ghế mây và tiếng phin nhỏ giọt</strong> — nơi mỗi ly cà phê đều rang xay nguyên bản ngay tại quán.</p>
      <ol class="steps"><li><span>01</span>Chọn hạt Robusta</li><li><span>02</span>Rang xay mỗi ngày</li><li><span>03</span>Pha phin nhỏ giọt</li></ol>
    </div>
  </div>
</section>

<section class="band-cta">
  <div class="container">
    <h2>Hãy tạo một khoảnh khắc đáng nhớ</h2>
    <a class="btn btn-cream" href="<?= url('/menu') ?>">Đặt hàng ngay →</a>
  </div>
</section>
