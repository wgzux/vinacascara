<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Cart.php';

function formatPrice(float $price): string { return number_format($price, 0, ',', '.') . '₫'; }
function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }

Auth::requireAdmin();
$adminPageTitle = 'Quản lý Đơn hàng';

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$where = 'WHERE 1=1';
$params = [];
if ($statusFilter) {
    $where .= ' AND o.order_status = ?';
    $params[] = $statusFilter;
}
if ($search) {
    $where .= ' AND (o.order_code LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ?)';
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

$total = Database::fetchOne("SELECT COUNT(*) as n FROM orders o $where", $params)['n'];
$orders = Database::fetchAll(
    "SELECT o.*, u.name as user_name FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     $where ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset",
    $params
);
$totalPages = ceil($total / $limit);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = $_POST['order_status'];
    $allowed = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    if (in_array($newStatus, $allowed)) {
        Database::update('orders', ['order_status' => $newStatus], 'id = ?', [$orderId]);
        Auth::setFlash('success', 'Cập nhật trạng thái thành công!');
    }
    redirect('/admin/orders');
}

$statusNames = [
    'pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý',
    'shipped'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy'
];
$statusColors = [
    'pending'=>'badge-yellow','confirmed'=>'badge-blue','processing'=>'badge-purple',
    'shipped'=>'badge-orange','delivered'=>'badge-green','cancelled'=>'badge-red'
];

require __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Đơn hàng (<?= $total ?>)</h3>
    <div class="header-actions">
      <form class="search-form" method="GET">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm mã đơn, tên, SĐT...">
        <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
        <button type="submit" class="btn-icon">🔍</button>
      </form>
    </div>
  </div>

  <!-- Status Filter Tabs -->
  <div class="filter-tabs">
    <a href="/admin/orders" class="filter-tab <?= !$statusFilter ? 'active' : '' ?>">Tất cả</a>
    <?php foreach ($statusNames as $key => $name): ?>
    <a href="/admin/orders?status=<?= $key ?>" class="filter-tab <?= $statusFilter === $key ? 'active' : '' ?>"><?= $name ?></a>
    <?php endforeach; ?>
  </div>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>SĐT</th>
          <th>Sản phẩm</th>
          <th>Tổng tiền</th>
          <th>Thanh toán</th>
          <th>Trạng thái</th>
          <th>Ngày đặt</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order):
          $items = Database::fetchAll("SELECT product_name, quantity FROM order_items WHERE order_id = ?", [$order['id']]);
        ?>
        <tr>
          <td><strong>#<?= e($order['order_code']) ?></strong></td>
          <td><?= e($order['shipping_name']) ?><br><small class="text-muted"><?= e($order['user_name'] ?? 'Khách') ?></small></td>
          <td><?= e($order['shipping_phone']) ?></td>
          <td>
            <?php foreach ($items as $item): ?>
            <div class="item-mini"><?= e($item['product_name']) ?> ×<?= $item['quantity'] ?></div>
            <?php endforeach; ?>
          </td>
          <td><strong><?= formatPrice($order['total']) ?></strong></td>
          <td>
            <span class="badge <?= $order['payment_method'] === 'vnpay' ? 'badge-blue' : 'badge-gray' ?>">
              <?= strtoupper($order['payment_method']) ?>
            </span>
            <br>
            <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-green' : 'badge-yellow' ?>" style="margin-top:2px">
              <?= $order['payment_status'] === 'paid' ? 'Đã TT' : 'Chưa TT' ?>
            </span>
          </td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
              <input type="hidden" name="update_status" value="1">
              <select name="order_status" onchange="this.form.submit()" class="status-select <?= $statusColors[$order['order_status']] ?? '' ?>">
                <?php foreach ($statusNames as $key => $name): ?>
                <option value="<?= $key ?>" <?= $order['order_status'] === $key ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><?= date('d/m/Y<br>H:i', strtotime($order['created_at'])) ?></td>
          <td>
            <div class="action-btns">
              <a href="/admin/orders/<?= $order['id'] ?>" class="icon-btn" title="Chi tiết"><?= adminIcon('eye') ?></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="/admin/orders?page=<?= $i ?>&status=<?= $statusFilter ?>&q=<?= urlencode($search) ?>" 
       class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
