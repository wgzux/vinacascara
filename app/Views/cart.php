<?php require __DIR__ . '/includes/header.php'; ?>

<section class="cart-section">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Giỏ Hàng</h1>
      <?php if (!empty($items)): ?>
      <p class="page-subtitle"><?= Cart::count() ?> sản phẩm trong giỏ</p>
      <?php endif; ?>
    </div>

    <?php if (empty($items)): ?>
    <div class="cart-empty">
      <div class="empty-icon">🛒</div>
      <h3>Giỏ hàng đang trống</h3>
      <p>Hãy thêm sản phẩm vào giỏ để bắt đầu mua sắm</p>
      <a href="/products" class="btn btn-primary btn-lg">Khám phá sản phẩm</a>
    </div>
    <?php else: ?>
    <div class="cart-grid">
      <!-- Cart Items -->
      <div class="cart-items" id="cartItems">
        <?php foreach ($items as $item): ?>
        <div class="cart-item" id="cartItem-<?= $item['id'] ?>">
          <div class="cart-item-image">
            <img src="<?= e($item['image'] ?? '') ?>" alt="<?= e($item['name']) ?>">
          </div>
          <div class="cart-item-info">
            <h3 class="cart-item-name">
              <a href="/product/<?= e($item['slug']) ?>"><?= e($item['name']) ?></a>
            </h3>
            <div class="cart-item-price-unit"><?= formatPrice($item['price']) ?>/sản phẩm</div>
          </div>
          <div class="cart-item-qty">
            <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">−</button>
            <span class="qty-display"><?= $item['quantity'] ?></span>
            <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
          </div>
          <div class="cart-item-subtotal"><?= formatPrice($item['subtotal']) ?></div>
          <button class="cart-item-remove" onclick="removeCartItem(<?= $item['id'] ?>)" title="Xóa">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
          </button>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Cart Summary -->
      <div class="cart-summary">
        <div class="summary-card">
          <h3 class="summary-title">Tóm tắt đơn hàng</h3>

          <div class="summary-row">
            <span>Tạm tính</span>
            <span id="subtotalDisplay"><?= formatPrice($totals['subtotal']) ?></span>
          </div>
          <div class="summary-row">
            <span>Phí vận chuyển</span>
            <span id="shippingDisplay">
              <?= $totals['shipping'] == 0 ? 'Miễn phí' : formatPrice($totals['shipping']) ?>
            </span>
          </div>
          <?php if ($totals['shipping'] > 0): ?>
          <div class="free-shipping-hint">
            Thêm <?= formatPrice(FREE_SHIPPING_THRESHOLD - $totals['subtotal']) ?> để được miễn phí vận chuyển
          </div>
          <?php endif; ?>

          <div class="summary-divider"></div>
          <div class="summary-row summary-total">
            <span>Tổng cộng</span>
            <span id="totalDisplay"><?= formatPrice($totals['total']) ?></span>
          </div>

          <a href="/checkout" class="btn btn-primary btn-lg btn-full checkout-btn">
            Tiến hành thanh toán
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="/products" class="btn btn-ghost btn-full">← Tiếp tục mua sắm</a>
        </div>

        <!-- Security Badge -->
        <div class="security-badges">
          <div class="security-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Thanh toán bảo mật</span>
          </div>
          <div class="security-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            <span>Đổi trả 7 ngày</span>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
