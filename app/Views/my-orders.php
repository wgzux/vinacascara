<?php require __DIR__ . '/includes/header.php'; ?>

<section class="orders-section">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Đơn Hàng Của Tôi</h1>
      <p class="page-subtitle"><?= count($orders) ?> đơn hàng</p>
    </div>

    <?php if (empty($orders)): ?>
    <div class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>Chưa có đơn hàng nào</h3>
      <p>Bắt đầu mua sắm để thấy đơn hàng của bạn ở đây</p>
      <a href="/products" class="btn btn-primary">Khám phá sản phẩm</a>
    </div>
    <?php else: ?>
    <div class="orders-list">
      <?php foreach ($orders as $order): ?>
      <?php 
        $statusInfo = $statusLabels[$order['order_status']] ?? ['label' => $order['order_status'], 'class' => ''];
        $items = $order['items'];
      ?>
      <div class="order-card">
        <div class="order-card-header">
          <div class="order-meta">
            <span class="order-code">#<?= e($order['order_code']) ?></span>
            <span class="order-date"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
          </div>
          <div class="order-badges">
            <span class="status-badge <?= $statusInfo['class'] ?>"><?= $statusInfo['label'] ?></span>
            <?php if ($order['payment_status'] === 'paid'): ?>
            <span class="status-badge status-paid">Đã thanh toán</span>
            <?php elseif ($order['payment_method'] === 'vnpay' && $order['payment_status'] === 'pending'): ?>
            <span class="status-badge status-pending-pay">Chờ thanh toán</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="order-items-preview">
          <?php foreach ($items as $item): ?>
          <div class="order-item-mini">
            <?php if ($item['product_image']): ?>
            <img src="<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>">
            <?php endif; ?>
            <div class="order-item-mini-info">
              <span class="item-mini-name"><?= e($item['product_name']) ?></span>
              <span class="item-mini-qty">x<?= $item['quantity'] ?></span>
              <span class="item-mini-price"><?= formatPrice($item['subtotal']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="order-card-footer">
          <div class="order-payment-info">
            <span><?= $order['payment_method'] === 'vnpay' ? 'VNPay' : 'COD' ?></span>
          </div>
          <div class="order-total">
            Tổng: <strong><?= formatPrice($order['total']) ?></strong>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
