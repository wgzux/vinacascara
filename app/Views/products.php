<?php require __DIR__ . '/includes/header.php'; ?>

<section class="products-page-section">
  <div class="container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Tất Cả Sản Phẩm</h1>
      <p class="page-subtitle">Khám phá bộ sưu tập trà Cascara đặc trưng của Vina Cascara</p>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
      <div class="filter-categories">
        <a href="/products" class="filter-tag <?= !$category ? 'active' : '' ?>">Tất cả</a>
        <?php foreach ($categories as $cat): ?>
        <a href="/products?category=<?= $cat['id'] ?>" 
           class="filter-tag <?= $category == $cat['id'] ? 'active' : '' ?>">
          <?= e($cat['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="filter-sort">
        <label for="sortSelect">Sắp xếp:</label>
        <select id="sortSelect" onchange="window.location='/products?sort='+this.value+'<?= $category ? '&category='.$category : '' ?>'">
          <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Nổi bật</option>
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
          <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Đánh giá cao</option>
        </select>
      </div>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
    <div class="empty-state">
      <div class="empty-icon">🍃</div>
      <h3>Không tìm thấy sản phẩm</h3>
      <p>Vui lòng thử lại với bộ lọc khác</p>
      <a href="/products" class="btn btn-primary">Xem tất cả</a>
    </div>
    <?php else: ?>
    <div class="products-grid products-grid-full">
      <?php foreach ($products as $product): ?>
      <div class="product-card">
        <div class="product-card-image">
          <div class="swiper product-swiper" id="sp-<?= $product['id'] ?>">
            <div class="swiper-wrapper">
              <?php foreach ($product['images'] as $img): ?>
              <div class="swiper-slide">
                <img src="<?= e($img['image_url']) ?>" 
                     alt="<?= e($img['alt_text'] ?? $product['name']) ?>" loading="lazy">
              </div>
              <?php endforeach; ?>
            </div>
            <?php if (count($product['images']) > 1): ?>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <?php endif; ?>
          </div>
          <?php if ($product['stock'] == 0): ?>
          <div class="product-badge badge-sold">Hết hàng</div>
          <?php endif; ?>
          <div class="product-quick-actions">
            <a href="/product/<?= e($product['slug']) ?>" class="quick-btn" title="Xem chi tiết">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
          </div>
        </div>
        <div class="product-card-info">
          <div class="product-rating">
            <div class="stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
              <svg viewBox="0 0 24 24" fill="<?= $i <= round($product['avg_rating']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
              <?php endfor; ?>
            </div>
            <span class="rating-count">(<?= $product['review_count'] ?>)</span>
          </div>
          <h3 class="product-name"><a href="/product/<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></h3>
          <p class="product-desc"><?= e($product['short_description']) ?></p>
          <div class="product-price-row">
            <div class="product-price">
              <span class="price-current"><?= formatPrice($product['price']) ?></span>
              <?php if ($product['compare_price']): ?>
              <span class="price-original"><?= formatPrice($product['compare_price']) ?></span>
              <?php endif; ?>
            </div>
            <div class="product-stock-badge <?= $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock') ?>">
              <?= $product['stock'] > 10 ? 'Còn hàng' : ($product['stock'] > 0 ? 'Còn ' . $product['stock'] : 'Hết') ?>
            </div>
          </div>
          <div class="product-card-actions">
            <?php if ($product['stock'] > 0): ?>
            <button class="btn btn-primary" onclick="addToCart(<?= $product['id'] ?>)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
              Thêm vào giỏ
            </button>
            <?php else: ?>
            <button class="btn btn-disabled" disabled>Hết hàng</button>
            <?php endif; ?>
            <a href="/product/<?= e($product['slug']) ?>" class="btn btn-outline">Chi tiết</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php foreach ($products as $product): ?>
      if (document.getElementById('sp-<?= $product['id'] ?>')) {
        new Swiper('#sp-<?= $product['id'] ?>', {
          loop: <?= count($product['images']) > 1 ? 'true' : 'false' ?>,
          pagination: { el: '.swiper-pagination', clickable: true },
          navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        });
      }
      <?php endforeach; ?>
    });
    </script>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
