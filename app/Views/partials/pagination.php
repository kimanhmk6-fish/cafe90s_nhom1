<?php /** @var App\Core\Pagination $pager */ if ($pager->totalPages > 1): ?>
<div class="pager-wrap">
<nav class="pager" aria-label="Phân trang">
  <?php if ($pager->page > 1): ?><a class="pager-arrow" href="<?= e($pager->link($pager->page - 1)) ?>" aria-label="Trang trước">‹</a><?php endif; ?>
  <?php $prev = 0; foreach ($pager->window() as $n): ?>
    <?php if ($prev && $n > $prev + 1): ?><span class="pager-gap">…</span><?php endif; ?>
    <a href="<?= e($pager->link($n)) ?>" class="<?= $n === $pager->page ? 'is-current' : '' ?>" <?= $n === $pager->page ? 'aria-current="page"' : '' ?>><?= $n ?></a>
  <?php $prev = $n; endforeach; ?>
  <?php if ($pager->page < $pager->totalPages): ?><a class="pager-arrow" href="<?= e($pager->link($pager->page + 1)) ?>" aria-label="Trang sau">›</a><?php endif; ?>
</nav>
</div>
<?php endif; ?>
