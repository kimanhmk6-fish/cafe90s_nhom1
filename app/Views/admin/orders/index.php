<header class="page-head row-between">
  <h1>Quản lý Đơn hàng</h1>
  <a class="btn btn-outline btn-lg" href="<?= url('/admin/orders/export?' . http_build_query(array_filter($filters))) ?>">Xuất CSV</a>
</header>
<form class="toolbar card-bar" method="get" role="search">
  <label>Từ ngày <input type="date" name="from" value="<?= e($filters['from']) ?>"></label>
  <label>Đến ngày <input type="date" name="to" value="<?= e($filters['to']) ?>"></label>
  <label>Trạng thái
    <select name="status"><option value="">Tất cả</option>
      <?php foreach (order_statuses() as $k => $l): ?><option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select></label>
  <label class="grow">Tìm kiếm <input type="search" name="q" placeholder="Mã đơn / SĐT / tên khách…" value="<?= e($filters['q']) ?>"></label>
  <button class="btn btn-dark btn-lg" type="submit" style="min-height:48px">Lọc</button>
</form>
<div class="panel table-wrap">
  <table class="table">
    <thead><tr><th scope="col"><span class="sr-only">STT</span></th><th scope="col">Mã đơn</th><th scope="col">Khách hàng</th><th scope="col">Thời gian</th><th scope="col" class="num">Tổng tiền</th><th scope="col">Thanh toán</th><th scope="col">Trạng thái</th><th scope="col"><span class="sr-only">Thao tác</span></th></tr></thead>
    <tbody>
    <?php foreach ($items as $n => $o): ?>
      <tr>
        <td class="row-no"><?= $pager->offset() + $n + 1 ?></td>
        <td><strong><?= e($o['order_code']) ?></strong></td>
        <td><?= e($o['customer_name']) ?><br><span class="muted small"><?= e($o['customer_phone']) ?></span></td>
        <td><?= date_vn($o['created_at']) ?></td>
        <td class="num"><?= money($o['total_amount']) ?></td>
        <td><?= pay_badge($o['pay_status'] ?? 'pending') ?></td>
        <td><?= status_badge($o['status']) ?></td>
        <td class="actions"><a href="<?= url('/admin/orders/' . (int) $o['id']) ?>">Xem</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?><tr><td colspan="8" class="empty-cell">Không có đơn hàng phù hợp.</td></tr><?php endif; ?>
    </tbody>
  </table>
  <div class="table-foot"><span class="muted small">Hiển thị <?= count($items) ?> / <?= (int) $pager->total ?> đơn</span><?php require BASE_PATH . '/app/Views/partials/pagination.php'; ?></div>
</div>
