<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Cart.php';

function formatPrice(float $price): string { return number_format($price, 0, ',', '.') . '₫'; }
function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }

Auth::requireAdmin();
$adminPageTitle = 'Khách hàng';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = trim($_GET['q'] ?? '');

$where = "WHERE role = 'customer'";
$params = [];
if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$total = Database::fetchOne("SELECT COUNT(*) as n FROM users $where", $params)['n'];
$customers = Database::fetchAll(
    "SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total), 0) as total_spent
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     $where
     GROUP BY u.id
     ORDER BY u.created_at DESC
     LIMIT $limit OFFSET $offset",
    $params
);
$totalPages = ceil($total / $limit);

require __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Khách hàng (<?= $total ?>)</h3>
    <form class="search-form" method="GET">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Tìm tên, email, SĐT...">
      <button type="submit" class="btn-icon">🔍</button>
    </form>
  </div>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Avatar</th>
          <th>Khách hàng</th>
          <th>Email</th>
          <th>Đăng nhập qua</th>
          <th>Số đơn</th>
          <th>Tổng chi</th>
          <th>Ngày đăng ký</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $cust): ?>
        <tr>
          <td>
            <?php if ($cust['avatar']): ?>
            <img src="<?= e($cust['avatar']) ?>" alt="Avatar" class="table-avatar">
            <?php else: ?>
            <div class="table-initials"><?= strtoupper(substr($cust['name'], 0, 1)) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= e($cust['name']) ?></strong>
            <?php if ($cust['phone']): ?>
            <br><small class="text-muted"><?= e($cust['phone']) ?></small>
            <?php endif; ?>
          </td>
          <td><?= e($cust['email']) ?></td>
          <td>
            <span class="badge <?= $cust['google_id'] ? 'badge-blue' : 'badge-gray' ?>">
              <?= $cust['google_id'] ? 'Google' : 'Email' ?>
            </span>
          </td>
          <td><?= $cust['order_count'] ?></td>
          <td><?= formatPrice($cust['total_spent']) ?></td>
          <td><?= date('d/m/Y', strtotime($cust['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="/admin/customers?page=<?= $i ?>&q=<?= urlencode($search) ?>" 
       class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
