<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Cart.php';

function formatPrice(float $price): string {
    return number_format($price, 0, ',', '.') . '₫';
}
function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }

Auth::requireAdmin();

$adminPageTitle = 'Dashboard';

// Stats
$stats = [
    'total_orders'    => Database::fetchOne("SELECT COUNT(*) as n FROM orders")['n'],
    'pending_orders'  => Database::fetchOne("SELECT COUNT(*) as n FROM orders WHERE order_status='pending'")['n'],
    'total_revenue'   => Database::fetchOne("SELECT COALESCE(SUM(total),0) as n FROM orders WHERE payment_status='paid' OR payment_method='cod'")['n'],
    'total_customers' => Database::fetchOne("SELECT COUNT(*) as n FROM users WHERE role='customer'")['n'],
    'total_products'  => Database::fetchOne("SELECT COUNT(*) as n FROM products WHERE status='active'")['n'],
    'low_stock'       => Database::fetchOne("SELECT COUNT(*) as n FROM products WHERE stock <= 5 AND status='active'")['n'],
];

// Recent orders
$recentOrders = Database::fetchAll(
    "SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 8"
);

// Monthly revenue for chart (last 6 months)
$monthlyRevenue = Database::fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue, COUNT(*) as orders
     FROM orders
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')
     ORDER BY month ASC"
);

require __DIR__ . '/../includes/admin_header.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
  <div class="stat-card stat-revenue">
    <div class="stat-icon">💰</div>
    <div class="stat-info">
      <div class="stat-value"><?= formatPrice($stats['total_revenue']) ?></div>
      <div class="stat-label">Doanh thu</div>
    </div>
  </div>
  <div class="stat-card stat-orders">
    <div class="stat-icon">📦</div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['total_orders'] ?></div>
      <div class="stat-label">Tổng đơn hàng</div>
    </div>
  </div>
  <div class="stat-card stat-customers">
    <div class="stat-icon">👥</div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['total_customers'] ?></div>
      <div class="stat-label">Khách hàng</div>
    </div>
  </div>
  <div class="stat-card stat-pending">
    <div class="stat-icon">⏳</div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['pending_orders'] ?></div>
      <div class="stat-label">Chờ xử lý</div>
    </div>
  </div>
</div>

<?php if ($stats['low_stock'] > 0): ?>
<div class="admin-alert admin-alert-warning">
  ⚠️ <strong><?= $stats['low_stock'] ?></strong> sản phẩm sắp hết hàng (còn ≤ 5).
  <a href="/admin/products">Quản lý kho hàng →</a>
</div>
<?php endif; ?>

<!-- Charts + Recent Orders -->
<div class="dashboard-grid">
  <!-- Revenue Chart -->
  <div class="admin-card">
    <div class="card-header">
      <h3>Doanh thu 6 tháng gần nhất</h3>
    </div>
    <div class="chart-container">
      <canvas id="revenueChart"></canvas>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="admin-card">
    <div class="card-header"><h3>Tổng quan kho</h3></div>
    <?php
    $products = Database::fetchAll("SELECT name, stock FROM products WHERE status='active' ORDER BY stock ASC");
    ?>
    <div class="stock-overview">
      <?php foreach ($products as $product): ?>
      <div class="stock-item">
        <span class="stock-name"><?= e($product['name']) ?></span>
        <div class="stock-bar-wrap">
          <div class="stock-bar" style="width:<?= min(100, ($product['stock'] / 200) * 100) ?>%; background:<?= $product['stock'] <= 5 ? '#ef4444' : ($product['stock'] <= 20 ? '#f59e0b' : '#22c55e') ?>"></div>
        </div>
        <span class="stock-num"><?= $product['stock'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Recent Orders -->
<div class="admin-card">
  <div class="card-header">
    <h3>Đơn hàng gần đây</h3>
    <a href="/admin/orders" class="btn-link">Xem tất cả →</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>Tổng tiền</th>
          <th>Thanh toán</th>
          <th>Trạng thái</th>
          <th>Ngày đặt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $order): ?>
        <tr>
          <td><strong>#<?= e($order['order_code']) ?></strong></td>
          <td><?= e($order['user_name'] ?? $order['guest_name'] ?? 'Khách vãng lai') ?></td>
          <td><?= formatPrice($order['total']) ?></td>
          <td>
            <span class="badge <?= $order['payment_method'] === 'vnpay' ? 'badge-blue' : 'badge-gray' ?>">
              <?= $order['payment_method'] === 'vnpay' ? 'VNPay' : 'COD' ?>
            </span>
          </td>
          <td>
            <?php
            $statusColors = [
              'pending' => 'badge-yellow', 'confirmed' => 'badge-blue',
              'processing' => 'badge-purple', 'shipped' => 'badge-orange',
              'delivered' => 'badge-green', 'cancelled' => 'badge-red'
            ];
            $statusNames = [
              'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận',
              'processing' => 'Đang xử lý', 'shipped' => 'Đang giao',
              'delivered' => 'Đã giao', 'cancelled' => 'Đã hủy'
            ];
            ?>
            <span class="badge <?= $statusColors[$order['order_status']] ?? '' ?>">
              <?= $statusNames[$order['order_status']] ?? $order['order_status'] ?>
            </span>
          </td>
          <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const months = <?= json_encode(array_column($monthlyRevenue, 'month')) ?>;
const revenues = <?= json_encode(array_column($monthlyRevenue, 'revenue')) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: months.map(m => {
      const [y, mo] = m.split('-');
      return `${mo}/${y}`;
    }),
    datasets: [{
      label: 'Doanh thu (₫)',
      data: revenues,
      backgroundColor: 'rgba(89, 47, 38, 0.8)',
      borderColor: '#592F26',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        ticks: {
          callback: (v) => new Intl.NumberFormat('vi-VN').format(v) + '₫'
        }
      }
    }
  }
});
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
