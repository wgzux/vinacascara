<?php require __DIR__ . '/includes/header.php'; ?>

<section class="checkout-section">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Thông Tin Thanh Toán</h1>
    </div>

    <!-- Checkout Steps -->
    <div class="checkout-steps">
      <div class="step active"><span>1</span> Giỏ hàng</div>
      <div class="step-connector"></div>
      <div class="step active"><span>2</span> Thanh toán</div>
      <div class="step-connector"></div>
      <div class="step"><span>3</span> Xác nhận</div>
    </div>

    <form method="POST" id="checkoutForm">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrf() ?>">
      <div class="checkout-grid">

        <!-- Left: Shipping Info -->
        <div class="checkout-left">
          <div class="checkout-card">
            <h2 class="card-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              Địa chỉ giao hàng
            </h2>

            <?php if (!$currentUser): ?>
            <div class="form-group">
              <label for="guest_email">Email (để nhận thông báo đơn hàng)</label>
              <input type="email" id="guest_email" name="guest_email" 
                     placeholder="email@example.com">
            </div>
            <?php endif; ?>

            <div class="form-row">
              <div class="form-group required">
                <label for="shipping_name">Tên người nhận *</label>
                <input type="text" id="shipping_name" name="shipping_name" required
                       value="<?= e($currentUser['name'] ?? '') ?>"
                       placeholder="Nguyễn Văn A">
              </div>
              <div class="form-group required">
                <label for="shipping_phone">Số điện thoại *</label>
                <input type="tel" id="shipping_phone" name="shipping_phone" required
                       placeholder="0xxx xxx xxx">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="shipping_province">Tỉnh/Thành phố</label>
                <input type="text" id="shipping_province" name="shipping_province" placeholder="TP. Hồ Chí Minh">
              </div>
              <div class="form-group">
                <label for="shipping_district">Quận/Huyện</label>
                <input type="text" id="shipping_district" name="shipping_district" placeholder="Quận 1">
              </div>
            </div>

            <div class="form-group required">
              <label for="shipping_address">Địa chỉ chi tiết *</label>
              <input type="text" id="shipping_address" name="shipping_address" required
                     placeholder="Số nhà, tên đường, phường/xã">
            </div>

            <div class="form-group">
              <label for="notes">Ghi chú</label>
              <textarea id="notes" name="notes" rows="3" 
                        placeholder="Chú ý giao hàng, thời gian giao..."></textarea>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="checkout-card">
            <h2 class="card-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              Phương thức thanh toán
            </h2>
            <div class="payment-methods">
              <label class="payment-option active" for="pay_cod">
                <input type="radio" id="pay_cod" name="payment_method" value="cod" checked
                       onchange="selectPayment(this)">
                <div class="payment-icon">💵</div>
                <div class="payment-info">
                  <strong>Thanh toán khi nhận hàng (COD)</strong>
                  <span>Trả tiền mặt khi nhận được hàng</span>
                </div>
                <div class="payment-check">✓</div>
              </label>
              <label class="payment-option" for="pay_vnpay">
                <input type="radio" id="pay_vnpay" name="payment_method" value="vnpay"
                       onchange="selectPayment(this)">
                <div class="payment-icon">
                  <img src="https://sandbox.vnpayment.vn/paymentv2/Content/images/logoVnpay.png" 
                       alt="VNPay" style="height:24px; border-radius:4px;">
                </div>
                <div class="payment-info">
                  <strong>Thanh toán VNPay</strong>
                  <span>ATM, Internet Banking, QR Code</span>
                </div>
                <div class="payment-check"></div>
              </label>
            </div>
          </div>
        </div>

        <!-- Right: Order Summary -->
        <div class="checkout-right">
          <div class="checkout-card summary-sticky">
            <h2 class="card-title">Đơn hàng của bạn</h2>

            <div class="order-items-list">
              <?php foreach ($items as $item): ?>
              <div class="order-item-row">
                <div class="order-item-img">
                  <img src="<?= e($item['image'] ?? '') ?>" alt="<?= e($item['name']) ?>">
                  <span class="item-qty-badge"><?= $item['quantity'] ?></span>
                </div>
                <div class="order-item-name"><?= e($item['name']) ?></div>
                <div class="order-item-price"><?= formatPrice($item['subtotal']) ?></div>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="summary-divider"></div>
            <div class="summary-row">
              <span>Tạm tính</span>
              <span><?= formatPrice($totals['subtotal']) ?></span>
            </div>
            <div class="summary-row">
              <span>Phí vận chuyển</span>
              <span><?= $totals['shipping'] == 0 ? 'Miễn phí' : formatPrice($totals['shipping']) ?></span>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-row summary-total">
              <span>Tổng cộng</span>
              <span class="total-amount"><?= formatPrice($totals['total']) ?></span>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-full" id="placeOrderBtn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              Đặt hàng ngay
            </button>

            <p class="checkout-agreement">
              Bằng cách đặt hàng, bạn đồng ý với <a href="#">điều khoản dịch vụ</a> của chúng tôi.
            </p>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
function selectPayment(input) {
  document.querySelectorAll('.payment-option').forEach(opt => {
    opt.classList.remove('active');
    opt.querySelector('.payment-check').textContent = '';
  });
  const label = input.closest('.payment-option');
  label.classList.add('active');
  label.querySelector('.payment-check').textContent = '✓';
}

document.getElementById('checkoutForm').addEventListener('submit', function() {
  document.getElementById('placeOrderBtn').textContent = 'Đang xử lý...';
  document.getElementById('placeOrderBtn').disabled = true;
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
