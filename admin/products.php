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
$adminPageTitle = 'Quản lý Sản phẩm';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int) $_POST['product_id'];
    Database::update('products', ['status' => 'inactive'], 'id = ?', [$id]);
    Auth::setFlash('success', 'Đã ẩn sản phẩm.');
    redirect('/admin/products');
}

// Handle quick stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = (int) $_POST['product_id'];
    $stock = max(0, (int) $_POST['stock']);
    Database::update('products', ['stock' => $stock], 'id = ?', [$id]);
    Auth::setFlash('success', 'Đã cập nhật tồn kho.');
    redirect('/admin/products');
}

$products = Database::fetchAll(
    "SELECT p.*, c.name as category_name,
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
            COALESCE(AVG(r.rating), 0) as avg_rating
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN reviews r ON r.product_id = p.id AND r.status = 'approved'
     GROUP BY p.id
     ORDER BY p.id ASC"
);

require __DIR__ . '/../app/Views/includes/admin_header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Sản phẩm (<?= count($products) ?>)</h3>
    <a href="/admin/products/add" class="btn btn-primary btn-sm">
      <?= adminIcon('plus') ?> Thêm sản phẩm
    </a>
  </div>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Ảnh</th>
          <th>Sản phẩm</th>
          <th>Giá</th>
          <th>Tồn kho</th>
          <th>Đánh giá</th>
          <th>Trạng thái</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
          <td>
            <?php if ($product['primary_image']): ?>
            <img src="<?= e($product['primary_image']) ?>" alt="<?= e($product['name']) ?>" 
                 class="table-thumb">
            <?php endif; ?>
          </td>
          <td>
            <strong><?= e($product['name']) ?></strong>
            <br><small class="text-muted"><?= e($product['slug']) ?></small>
          </td>
          <td><?= formatPrice($product['price']) ?></td>
          <td>
            <form method="POST" class="inline-form">
              <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
              <input type="hidden" name="update_stock" value="1">
              <input type="number" name="stock" value="<?= $product['stock'] ?>" 
                     class="stock-input <?= $product['stock'] <= 5 ? 'stock-low' : '' ?>" min="0">
              <button type="submit" class="btn-save-sm">✓</button>
            </form>
          </td>
          <td>
            <span class="stars-small">
              <?= number_format($product['avg_rating'], 1) ?> ★
            </span>
          </td>
          <td>
            <span class="badge <?= $product['status'] === 'active' ? 'badge-green' : 'badge-red' ?>">
              <?= $product['status'] === 'active' ? 'Đang bán' : 'Ẩn' ?>
            </span>
          </td>
          <td>
            <div class="action-btns">
              <a href="/product/<?= e($product['slug']) ?>" target="_blank" class="icon-btn" title="Xem"><?= adminIcon('eye') ?></a>
              <a href="/admin/products/edit/<?= $product['id'] ?>" class="icon-btn" title="Sửa"><?= adminIcon('edit') ?></a>
              <form method="POST" style="display:inline" 
                    onsubmit="return confirm('Ẩn sản phẩm này?')">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="delete_product" value="1">
                <button type="submit" class="icon-btn icon-btn-danger" title="Ẩn"><?= adminIcon('trash') ?></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../app/Views/includes/admin_footer.php'; ?>
