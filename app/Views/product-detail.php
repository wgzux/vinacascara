<?php require __DIR__ . '/includes/header.php'; ?>

<section class="product-detail-section">
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="/">Trang chủ</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="/products">Sản phẩm</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span><?= e($product['name']) ?></span>
    </nav>

    <div class="product-detail-grid">
      <!-- Gallery -->
      <div class="product-gallery">
        <!-- Main Swiper -->
        <div class="swiper gallery-main" id="galleryMain">
          <div class="swiper-wrapper">
            <?php foreach ($images as $img): ?>
            <div class="swiper-slide">
              <div class="gallery-slide">
                <img src="<?= e($img['image_url']) ?>" 
                     alt="<?= e($img['alt_text'] ?? $product['name']) ?>">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>

        <!-- Thumbnail Swiper -->
        <?php if (count($images) > 1): ?>
        <div class="swiper gallery-thumbs" id="galleryThumbs">
          <div class="swiper-wrapper">
            <?php foreach ($images as $img): ?>
            <div class="swiper-slide">
              <img src="<?= e($img['image_url']) ?>" alt="Thumbnail">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Product Info -->
      <div class="product-detail-info">
        <?php if ($product['category_name']): ?>
        <div class="product-category-tag"><?= e($product['category_name']) ?></div>
        <?php endif; ?>

        <h1 class="product-detail-name"><?= e($product['name']) ?></h1>

        <div class="product-rating-row">
          <div class="stars-display">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?= $i <= round($product['avg_rating']) ? '★' : '☆' ?>
            <?php endfor; ?>
          </div>
          <span class="rating-label"><?= number_format($product['avg_rating'], 1) ?>/5</span>
          <span class="rating-count">(<?= $product['review_count'] ?> đánh giá)</span>
        </div>

        <div class="product-detail-price">
          <span class="price-current"><?= formatPrice($product['price']) ?></span>
          <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
          <span class="price-original"><?= formatPrice($product['compare_price']) ?></span>
          <span class="price-discount">
            -<?= round((1 - $product['price']/$product['compare_price'])*100) ?>%
          </span>
          <?php endif; ?>
        </div>

        <p class="product-short-desc"><?= e($product['short_description']) ?></p>

        <!-- Stock status -->
        <div class="stock-status <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
          <span class="stock-dot"></span>
          <?= $product['stock'] > 0 ? 'Còn hàng (' . $product['stock'] . ' sản phẩm)' : 'Hết hàng' ?>
        </div>

        <?php if ($product['stock'] > 0): ?>
        <!-- Add to Cart Form -->
        <div class="add-to-cart-form">
          <div class="qty-selector">
            <button type="button" class="qty-btn" onclick="changeQty(-1)" id="qtyMinus">−</button>
            <input type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>" 
                   class="qty-input" onchange="validateQty()">
            <button type="button" class="qty-btn" onclick="changeQty(1)" id="qtyPlus">+</button>
          </div>
          <button class="btn btn-primary btn-lg btn-add-cart-detail"
                  onclick="addToCartDetail(<?= $product['id'] ?>)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            Thêm vào giỏ hàng
          </button>
          <a href="/cart" class="btn btn-outline btn-lg" 
             onclick="return addToCartAndGo(<?= $product['id'] ?>)">
            Mua ngay
          </a>
        </div>
        <?php endif; ?>

        <!-- Product Tabs -->
        <div class="product-tabs">
          <div class="tab-buttons">
            <button class="tab-btn active" onclick="switchTab('description', this)">Mô tả</button>
            <button class="tab-btn" onclick="switchTab('howto', this)">Cách pha</button>
          </div>
          <div class="tab-content active" id="tab-description">
            <?= $product['description'] ?>
          </div>
          <div class="tab-content" id="tab-howto">
            <h4>Hướng dẫn pha trà Cascara</h4>
            <ol>
              <li>Đong 10-15g trà Cascara (khoảng 2-3 muỗng canh)</li>
              <li>Đun nước đến 85-90°C (không dùng nước đang sôi)</li>
              <li>Rót 300-400ml nước vào bình hoặc ly có lọc</li>
              <li>Ngâm 5-7 phút, khuấy nhẹ</li>
              <li>Lọc bỏ bã và thưởng thức</li>
            </ol>
            <p><strong>Mẹo:</strong> Có thể thêm mật ong, cam quýt, hoặc uống lạnh với đá.</p>
          </div>
        </div>

        <!-- Sharing/policy -->
        <div class="product-policy">
          <div class="policy-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
            <span>Miễn phí vận chuyển đơn từ 300.000₫</span>
          </div>
          <div class="policy-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Đảm bảo chất lượng sản phẩm</span>
          </div>
          <div class="policy-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            <span>Đổi trả trong 7 ngày</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
      <h2 class="reviews-title">Đánh giá sản phẩm (<?= count($reviews) ?>)</h2>

      <!-- Review Summary -->
      <?php if (count($reviews) > 0): ?>
      <div class="review-summary">
        <div class="rating-big"><?= number_format($product['avg_rating'], 1) ?></div>
        <div class="rating-detail">
          <div class="stars-display-lg">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?= $i <= round($product['avg_rating']) ? '★' : '☆' ?>
            <?php endfor; ?>
          </div>
          <span><?= $product['review_count'] ?> đánh giá</span>
        </div>
      </div>
      <?php endif; ?>

      <!-- Review List -->
      <div class="reviews-list">
        <?php foreach ($reviews as $review): ?>
        <div class="review-item">
          <div class="review-header">
            <div class="reviewer-info">
              <?php if ($review['user_avatar']): ?>
              <img src="<?= e($review['user_avatar']) ?>" alt="Avatar" class="reviewer-avatar">
              <?php else: ?>
              <div class="reviewer-initials"><?= strtoupper(substr($review['user_name'], 0, 1)) ?></div>
              <?php endif; ?>
              <div>
                <strong class="reviewer-name"><?= e($review['user_name']) ?></strong>
                <span class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
              </div>
            </div>
            <div class="review-stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <?= $i <= $review['rating'] ? '★' : '☆' ?>
              <?php endfor; ?>
            </div>
          </div>
          <?php if ($review['title']): ?>
          <h4 class="review-title"><?= e($review['title']) ?></h4>
          <?php endif; ?>
          <p class="review-comment"><?= e($review['comment']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Write Review Form -->
      <?php if (Auth::isLoggedIn()): ?>
      <div class="review-form-wrap">
        <h3>Viết đánh giá của bạn</h3>
        <form method="POST" class="review-form">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrf() ?>">
          <input type="hidden" name="submit_review" value="1">
          <div class="form-group">
            <label>Đánh giá</label>
            <div class="star-picker" id="starPicker">
              <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="star-pick" data-val="<?= $i ?>" onclick="pickStar(<?= $i ?>)">★</span>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="5">
          </div>
          <div class="form-group">
            <label for="reviewComment">Nhận xét</label>
            <textarea id="reviewComment" name="comment" rows="4" required
                      placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
        </form>
      </div>
      <?php else: ?>
      <div class="review-login-prompt">
        <p>Vui lòng <a href="/login">đăng nhập</a> để viết đánh giá.</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <div class="related-section">
      <h2 class="related-title">Sản Phẩm Liên Quan</h2>
      <div class="related-grid">
        <?php foreach ($related as $rel): ?>
        <a href="/product/<?= e($rel['slug']) ?>" class="related-card">
          <div class="related-image">
            <img src="<?= e($rel['primary_image'] ?? '') ?>" alt="<?= e($rel['name']) ?>" loading="lazy">
          </div>
          <div class="related-info">
            <h4><?= e($rel['name']) ?></h4>
            <span><?= formatPrice($rel['price']) ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
// Gallery Swipers
document.addEventListener('DOMContentLoaded', function() {
  var thumbs = new Swiper('#galleryThumbs', {
    spaceBetween: 10,
    slidesPerView: 4,
    freeMode: true,
    watchSlidesProgress: true,
  });
  var main = new Swiper('#galleryMain', {
    spaceBetween: 10,
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    thumbs: { swiper: thumbs },
  });
});

function changeQty(delta) {
  var input = document.getElementById('qtyInput');
  var val = parseInt(input.value) + delta;
  var max = parseInt(input.max);
  input.value = Math.max(1, Math.min(max, val));
}

function validateQty() {
  var input = document.getElementById('qtyInput');
  var val = parseInt(input.value);
  input.value = Math.max(1, Math.min(parseInt(input.max), isNaN(val) ? 1 : val));
}

function addToCartDetail(productId) {
  var qty = parseInt(document.getElementById('qtyInput').value);
  addToCart(productId, qty);
}

async function addToCartAndGo(productId) {
  var qty = parseInt(document.getElementById('qtyInput').value);
  await addToCart(productId, qty);
  window.location.href = '/cart';
  return false;
}

function switchTab(tab, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  btn.classList.add('active');
}

function pickStar(val) {
  document.getElementById('ratingInput').value = val;
  document.querySelectorAll('.star-pick').forEach((s, i) => {
    s.classList.toggle('active', i < val);
  });
}
pickStar(5);
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
