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

// Get Product ID from URL
global $router;
// $id comes from the route parameter
$id = (int)$id;

$product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
if (!$product) {
    die("Sản phẩm không tồn tại.");
}

$categories = Database::fetchAll("SELECT * FROM categories");
$images = Database::fetchAll("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC", [$id]);
$currentImageUrls = implode("\n", array_column($images, 'image_url'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $slug  = trim($_POST['slug'] ?? preg_replace('/[^a-z0-9]+/', '-', strtolower($name)));
    $price = (float)($_POST['price'] ?? 0);
    $comparePrice = !empty($_POST['compare_price']) ? (float)$_POST['compare_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $shortDesc = trim($_POST['short_description'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0) ?: null;
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';

    if ($name && $price > 0) {
        Database::update('products', [
            'category_id'       => $catId,
            'name'              => $name,
            'slug'              => $slug,
            'short_description' => $shortDesc,
            'description'       => $desc,
            'price'             => $price,
            'compare_price'     => $comparePrice,
            'stock'             => $stock,
            'featured'          => $featured,
            'status'            => $status,
        ], 'id = ?', [$id]);

        // Update images (Delete existing and re-insert for simplicity)
        Database::query("DELETE FROM product_images WHERE product_id = ?", [$id]);
        $imageUrls = array_filter(array_map('trim', explode("\n", $_POST['image_urls'] ?? '')));
        foreach ($imageUrls as $i => $url) {
            if ($url) {
                Database::insert('product_images', [
                    'product_id' => $id,
                    'image_url'  => $url,
                    'alt_text'   => $name,
                    'sort_order' => $i,
                    'is_primary' => $i === 0 ? 1 : 0,
                ]);
            }
        }

        Auth::setFlash('success', 'Cập nhật sản phẩm thành công!');
        redirect('/admin/products');
    } else {
        Auth::setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    }
}

$adminPageTitle = 'Sửa Sản phẩm: ' . $product['name'];
require __DIR__ . '/../app/Views/includes/admin_header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Sửa sản phẩm: <?= e($product['name']) ?></h3>
    <a href="/admin/products" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="POST" class="admin-form">
    <div class="form-grid-2">
      <div class="form-group" style="grid-column: span 2">
         <label>Trạng thái hiển thị</label>
         <select name="status">
            <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Đang bán (Active)</option>
            <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Ẩn (Inactive)</option>
         </select>
      </div>
      <div class="form-group">
        <label>Tên sản phẩm *</label>
        <input type="text" name="name" value="<?= e($product['name']) ?>" required oninput="genSlug(this.value)">
      </div>
      <div class="form-group">
        <label>Slug URL *</label>
        <input type="text" name="slug" id="slugInput" value="<?= e($product['slug']) ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả ngắn</label>
      <textarea name="short_description" rows="2"><?= e($product['short_description']) ?></textarea>
    </div>

    <div class="form-group">
      <label>Mô tả đầy đủ (HTML)</label>
      <textarea name="description" rows="8"><?= e($product['description']) ?></textarea>
    </div>

    <div class="form-grid-3">
      <div class="form-group">
        <label>Giá bán (₫) *</label>
        <input type="number" name="price" value="<?= $product['price'] ?>" required min="0" step="1000">
      </div>
      <div class="form-group">
        <label>Giá gốc (₫)</label>
        <input type="number" name="compare_price" value="<?= $product['compare_price'] ?>" min="0" step="1000">
      </div>
      <div class="form-group">
        <label>Tồn kho</label>
        <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Danh mục</label>
        <select name="category_id">
          <option value="">-- Chọn danh mục --</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group form-checkbox">
        <label>
          <input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>>
          <span>Sản phẩm nổi bật</span>
        </label>
      </div>
    </div>

    <div class="form-group">
      <label>URLs ảnh sản phẩm (mỗi dòng 1 URL)</label>
      <textarea name="image_urls" rows="6"><?= e($currentImageUrls) ?></textarea>
      <small class="form-hint">Dòng đầu tiên sẽ là ảnh chính.</small>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      <a href="/admin/products" class="btn btn-outline">Hủy</a>
    </div>
  </form>
</div>

<script>
function genSlug(name) {
  const slug = name.toLowerCase()
    .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a')
    .replace(/[èéẹẻẽêềếệểễ]/g, 'e')
    .replace(/[ìíịỉĩ]/g, 'i')
    .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o')
    .replace(/[ùúụủũưừứựửữ]/g, 'u')
    .replace(/[ỳýỵỷỹ]/g, 'y')
    .replace(/[đ]/g, 'd')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-');
  document.getElementById('slugInput').value = slug;
}
</script>

<?php require __DIR__ . '/../app/Views/includes/admin_footer.php'; ?>
