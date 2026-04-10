<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Cart.php';

if (!function_exists('formatPrice')) {
    function formatPrice(float $price): string { return number_format($price, 0, ',', '.') . '₫'; }
}
if (!function_exists('e')) {
    function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('redirect')) {
    function redirect(string $url): void { header('Location: ' . $url); exit; }
}

Auth::requireAdmin();
$adminPageTitle = 'Quản lý Đánh giá';

// Handle approve/reject/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['review_id'];
    if (isset($_POST['approve'])) {
        Database::update('reviews', ['status' => 'approved'], 'id = ?', [$id]);
    } elseif (isset($_POST['reject'])) {
        Database::update('reviews', ['status' => 'rejected'], 'id = ?', [$id]);
    } elseif (isset($_POST['delete'])) {
        Database::query("DELETE FROM reviews WHERE id = ?", [$id]);
    }
    redirect('/admin/reviews');
}

$reviews = Database::fetchAll(
    "SELECT r.*, u.name as user_name, p.name as product_name, p.slug as product_slug
     FROM reviews r
     JOIN users u ON u.id = r.user_id
     JOIN products p ON p.id = r.product_id
     ORDER BY r.created_at DESC"
);

$statusFilter = $_GET['status'] ?? '';
if ($statusFilter) {
    $reviews = array_filter($reviews, fn($r) => $r['status'] === $statusFilter);
}

require __DIR__ . '/../app/Views/includes/admin_header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Đánh giá sản phẩm</h3>
    <div class="filter-tabs">
      <a href="/admin/reviews" class="filter-tab <?= !$statusFilter ? 'active' : '' ?>">Tất cả</a>
      <a href="/admin/reviews?status=approved" class="filter-tab <?= $statusFilter==='approved' ? 'active' : '' ?>">Đã duyệt</a>
      <a href="/admin/reviews?status=pending" class="filter-tab <?= $statusFilter==='pending' ? 'active' : '' ?>">Chờ duyệt</a>
      <a href="/admin/reviews?status=rejected" class="filter-tab <?= $statusFilter==='rejected' ? 'active' : '' ?>">Từ chối</a>
    </div>
  </div>

  <div class="reviews-admin-list">
    <?php foreach ($reviews as $review): ?>
    <div class="review-admin-card">
      <div class="review-admin-header">
        <div class="review-admin-meta">
          <strong><?= e($review['user_name']) ?></strong>
          <span>về</span>
          <a href="/product/<?= e($review['product_slug']) ?>" target="_blank">
            <?= e($review['product_name']) ?>
          </a>
          <span class="stars-text">
            <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
          </span>
        </div>
        <div class="review-admin-date"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></div>
      </div>

      <?php if ($review['title']): ?>
      <div class="review-title-text"><strong><?= e($review['title']) ?></strong></div>
      <?php endif; ?>
      <p class="review-comment-text"><?= e($review['comment']) ?></p>

      <div class="review-admin-actions">
        <span class="badge <?= match($review['status']) {
          'approved' => 'badge-green',
          'rejected' => 'badge-red',
          default => 'badge-yellow'
        } ?>"><?= match($review['status']) {
          'approved' => 'Đã duyệt',
          'rejected' => 'Từ chối',
          default => 'Chờ duyệt'
        } ?></span>

        <div class="action-btns">
          <?php if ($review['status'] !== 'approved'): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
            <button type="submit" name="approve" class="btn btn-sm badge-green">✓ Duyệt</button>
          </form>
          <?php endif; ?>
          <?php if ($review['status'] !== 'rejected'): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
            <button type="submit" name="reject" class="btn btn-sm badge-red">✗ Từ chối</button>
          </form>
          <?php endif; ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('Xóa đánh giá này?')">
            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
            <button type="submit" name="delete" class="btn btn-sm badge-gray">Xóa</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../app/Views/includes/admin_footer.php'; ?>
