<?php require __DIR__ . '/includes/header.php'; ?>

<section class="success-section">
  <div class="container">
    <div class="success-card">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
      </div>
      <h1 class="success-title">Đặt hàng thành công!</h1>
      <p class="success-subtitle">Cảm ơn bạn đã tin tưởng Vina Cascara 🍃</p>
      <div class="order-code-display">
        Mã đơn hàng: <strong><?= e($orderCode) ?></strong>
      </div>

      <?php if ($order): ?>
      <div class="order-summary-box">
        <div class="summary-row">
          <span>Người nhận</span>
          <span><?= e($order['shipping_name']) ?></span>
        </div>
        <div class="summary-row">
          <span>Số điện thoại</span>
          <span><?= e($order['shipping_phone']) ?></span>
        </div>
        <div class="summary-row">
          <span>Địa chỉ</span>
          <span><?= e($order['shipping_address']) ?></span>
        </div>
        <div class="summary-row">
          <span>Thanh toán</span>
          <span><?= $order['payment_method'] === 'vnpay' ? 'VNPay' : 'Tiền mặt (COD)' ?></span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-row summary-total">
          <span>Tổng cộng</span>
          <span><?= formatPrice($order['total']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <div class="success-actions">
        <?php if (Auth::isLoggedIn()): ?>
        <a href="/my-orders" class="btn btn-outline">Xem đơn hàng của tôi</a>
        <?php endif; ?>
        <a href="/products" class="btn btn-primary">Tiếp tục mua sắm</a>
      </div>

      <div class="success-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php if ($order && $order['payment_method'] === 'cod'): ?>
        Chúng tôi sẽ liên hệ xác nhận đơn hàng trong vòng 1-2 giờ làm việc.
        <?php else: ?>
        Thanh toán đã được xác nhận. Đơn hàng sẽ được xử lý ngay.
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
