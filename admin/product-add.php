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
$adminPageTitle = 'Thêm Sản phẩm';

$categories = Database::fetchAll("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $slug  = trim($_POST['slug'] ?? preg_replace('/[^a-z0-9]+/', '-', strtolower($name)));
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $shortDesc = trim($_POST['short_description'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0) ?: null;
    $featured = isset($_POST['featured']) ? 1 : 0;

    if ($name && $price > 0) {
        $id = Database::insert('products', [
            'category_id'       => $catId,
            'name'              => $name,
            'slug'              => $slug,
            'short_description' => $shortDesc,
            'description'       => $desc,
            'price'             => $price,
            'stock'             => $stock,
            'featured'          => $featured,
            'status'            => 'active',
        ]);

        // Add images
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

        Auth::setFlash('success', 'Thêm sản phẩm thành công!');
        redirect('/admin/products');
    } else {
        Auth::setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    }
}

require __DIR__ . '/../app/Views/includes/admin_header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Thêm sản phẩm mới</h3>
    <a href="/admin/products" class="btn btn-outline btn-sm">← Quay lại</a>
  </div>

  <form method="POST" class="admin-form">
    <div class="form-grid-2">
      <div class="form-group">
        <label>Tên sản phẩm *</label>
        <input type="text" name="name" required placeholder="Trà Cascara Nguyên Chất" oninput="genSlug(this.value)">
      </div>
      <div class="form-group">
        <label>Slug URL *</label>
        <input type="text" name="slug" id="slugInput" required placeholder="tra-cascara-nguyen-chat">
      </div>
    </div>

    <div class="form-group">
      <label>Mô tả ngắn</label>
      <textarea name="short_description" rows="2" placeholder="Mô tả ngắn gọn hiển thị trên card sản phẩm"></textarea>
    </div>

    <div class="form-group">
      <label>Mô tả đầy đủ (HTML)</label>
      <textarea name="description" rows="8" placeholder="Mô tả chi tiết sản phẩm, có thể dùng HTML"></textarea>
    </div>

    <div class="form-grid-3">
      <div class="form-group">
        <label>Giá bán (₫) *</label>
        <input type="number" name="price" required min="0" step="1000" placeholder="120000">
      </div>
      <div class="form-group">
        <label>Giá gốc (₫)</label>
        <input type="number" name="compare_price" min="0" step="1000" placeholder="150000">
      </div>
      <div class="form-group">
        <label>Tồn kho</label>
        <input type="number" name="stock" min="0" value="100">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label>Danh mục</label>
        <select name="category_id">
          <option value="">-- Chọn danh mục --</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group form-checkbox">
        <label>
          <input type="checkbox" name="featured" checked>
          <span>Sản phẩm nổi bật</span>
        </label>
      </div>
    </div>

    <div class="form-group">
      <label>URLs ảnh sản phẩm (mỗi dòng 1 URL)</label>
      <textarea name="image_urls" rows="4" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"></textarea>
      <small class="form-hint">Dòng đầu tiên sẽ là ảnh chính. Bạn có thể thêm nhiều ảnh để tạo carousel.</small>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
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
