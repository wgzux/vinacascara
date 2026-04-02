<?php require __DIR__ . '/includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero" id="hero">
  <div class="hero-bg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-badge">🌿 100% Tự Nhiên · Arabica Việt Nam</div>
    <h1 class="hero-title">
      <span class="title-line">VINA</span>
      <span class="title-line title-accent">CASCARA</span>
    </h1>
    <p class="hero-subtitle">Trà từ vỏ cà phê Arabica<br>Việt Nam thuần chất</p>
    <div class="hero-actions">
      <a href="#products" class="btn btn-primary btn-lg" id="heroExploreBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Khám phá sản phẩm
      </a>
      <a href="#about" class="btn btn-ghost btn-lg">
        Về chúng tôi
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><span class="stat-number">100%</span><span class="stat-label">Arabica</span></div>
      <div class="stat-divider"></div>
      <div class="hero-stat"><span class="stat-number">Thủ công</span><span class="stat-label">Phơi sấy</span></div>
      <div class="stat-divider"></div>
      <div class="hero-stat"><span class="stat-number">Tự nhiên</span><span class="stat-label">Không phụ gia</span></div>
    </div>
  </div>
  <div class="hero-scroll">
    <div class="scroll-indicator">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
  </div>
</section>

<!-- Products Section -->
<section class="section products-section" id="products">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Bộ sưu tập</span>
      <h2 class="section-title">Sản Phẩm Vina Cascara</h2>
      <p class="section-subtitle">Mỗi sản phẩm là tinh hoa từ những vùng cà phê Arabica nổi tiếng của Việt Nam</p>
    </div>

    <div class="products-grid" id="productsGrid">
      <?php foreach ($products as $product): ?>
      <div class="product-card" data-id="<?= $product['id'] ?>">
        <!-- Image Carousel -->
        <div class="product-card-image">
          <div class="swiper product-swiper" id="swiper-<?= $product['id'] ?>">
            <div class="swiper-wrapper">
              <?php foreach ($product['images'] as $img): ?>
              <div class="swiper-slide">
                <img src="<?= e($img['image_url']) ?>" 
                     alt="<?= e($img['alt_text'] ?? $product['name']) ?>"
                     loading="lazy">
              </div>
              <?php endforeach; ?>
            </div>
            <?php if (count($product['images']) > 1): ?>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <?php endif; ?>
          </div>

          <!-- Badges -->
          <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
          <div class="product-badge badge-sale">
            -<?= round((1 - $product['price']/$product['compare_price'])*100) ?>%
          </div>
          <?php endif; ?>
          <?php if ($product['stock'] == 0): ?>
          <div class="product-badge badge-sold">Hết hàng</div>
          <?php endif; ?>

          <!-- Quick actions -->
          <div class="product-quick-actions">
            <a href="/product/<?= e($product['slug']) ?>" class="quick-btn" title="Xem chi tiết">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
          </div>
        </div>

        <!-- Product Info -->
        <div class="product-card-info">
          <h3 class="product-name">
            <a href="/product/<?= e($product['slug']) ?>"><?= e($product['name']) ?></a>
          </h3>
          <p class="product-desc"><?= e($product['short_description']) ?></p>

          <div class="product-features">
            <!-- Render description which contains ul/li -->
            <?= $product['description'] ?>
          </div>

          <div class="product-price-row">
            <div class="product-price">
              <span class="price-current"><?= formatPrice($product['price']) ?></span>
            </div>
            <div class="product-spec">
              <?= e($product['specification'] ?? '') ?>
            </div>
          </div>

          <div class="product-card-actions">
            <?php if ($product['stock'] > 0): ?>
            <button class="btn btn-primary btn-add-cart" 
                    data-product-id="<?= $product['id'] ?>"
                    onclick="addToCart(<?= $product['id'] ?>)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
              Thêm vào giỏ
            </button>
            <a href="/product/<?= e($product['slug']) ?>" class="btn btn-outline" style="min-width:110px;">Mua ngay</a>
            <?php else: ?>
            <button class="btn btn-disabled" disabled>Hết hàng</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
      <a href="/products" class="btn btn-outline btn-lg">
        Xem tất cả sản phẩm
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- About Section -->
<section class="section about-section" id="about">
  <div class="container">
    <div class="about-grid">
      <div class="about-image-wrap">
        <div class="about-image-frame">
          <img src="/assets/images/about-tea.png" 
               alt="Về Vina Cascara" loading="lazy">
        </div>
        </div>
      </div>

      <div class="about-content">
        <span class="section-tag">Câu chuyện của chúng tôi</span>
        <h2 class="section-title">Vina Cascara – Trà từ vỏ cà phê Arabica Việt Nam</h2>
        <p class="about-text">
          Cascara là thức uống làm từ vỏ trái cà phê – phần thường bị bỏ đi trong quá trình chế biến. 
          Vina Cascara đã biến phần "phụ phẩm" này thành một sản phẩm cao cấp, tinh tế và đậm chất Việt.
        </p>
        <p class="about-text">
          Chúng tôi cộng tác trực tiếp với các nông dân trồng cà phê Arabica tại cao nguyên Việt Nam, 
          thu hoạch thủ công và phơi sấy trong điều kiện tự nhiên để giữ trọn hương vị.
        </p>

        <div class="about-features">
          <div class="feature-item">
            <div class="feature-icon">🌱</div>
            <div class="feature-text">
              <strong>100% Tự nhiên</strong>
              <span>Không chất bảo quản, không phẩm màu</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">☕</div>
            <div class="feature-text">
              <strong>Arabica Việt Nam</strong>
              <span>Thu hoạch thủ công tại vùng cao nguyên</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">✨</div>
            <div class="feature-text">
              <strong>Hương vị độc đáo</strong>
              <span>Thơm mùi trái cây khô, vị ngọt thanh tự nhiên</span>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">📦</div>
            <div class="feature-text">
              <strong>Đóng gói cẩn thận</strong>
              <span>Giữ hương vị tươi ngon đến tay bạn</span>
            </div>
          </div>
        </div>

        <a href="/products" class="btn btn-primary btn-lg">
          Khám phá ngay
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter / Contact Section -->
<section class="section contact-section" id="consultation">
  <div class="container">
    <div class="contact-inner">
      <div class="contact-content">
        <span class="section-tag">Liên hệ</span>
        <h2 class="section-title">Tư Vấn Pha Trà</h2>
        <p class="contact-desc">Để lại thông tin, chúng tôi sẽ hướng dẫn bạn cách pha cascara ngon nhất và tư vấn sản phẩm phù hợp.</p>
      </div>
      <form class="contact-form" method="POST" action="/api/contact.php" id="contactForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrf() ?>">
        <div class="form-row">
          <div class="form-group">
            <label for="contact_name">Họ và tên</label>
            <input type="text" id="contact_name" name="name" required placeholder="Nguyễn Văn A">
          </div>
          <div class="form-group">
            <label for="contact_phone">Số điện thoại</label>
            <input type="tel" id="contact_phone" name="phone" required placeholder="0xxx xxx xxx">
          </div>
        </div>
        <div class="form-group">
          <label for="contact_message">Tin nhắn</label>
          <textarea id="contact_message" name="message" rows="4" placeholder="Bạn muốn hỏi gì về sản phẩm?"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-full">
          Gửi yêu cầu tư vấn
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </form>
    </div>
  </div>
</section>

<!-- Init Swipers for product cards -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($products as $product): ?>
    if (document.getElementById('swiper-<?= $product['id'] ?>')) {
      new Swiper('#swiper-<?= $product['id'] ?>', {
        loop: <?= count($product['images']) > 1 ? 'true' : 'false' ?>,
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        autoplay: { delay: 3500, disableOnInteraction: false },
      });
    }
    <?php endforeach; ?>
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
