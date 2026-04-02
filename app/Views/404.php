<?php
$pageTitle = '404 – Không tìm thấy trang';
require __DIR__ . '/../includes/header.php';
?>
<section class="error-section">
  <div class="container">
    <div class="error-content">
      <div class="error-code">404</div>
      <h1>Trang không tồn tại</h1>
      <p>Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.</p>
      <div class="error-actions">
        <a href="/" class="btn btn-primary">Về trang chủ</a>
        <a href="/products" class="btn btn-outline">Xem sản phẩm</a>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
