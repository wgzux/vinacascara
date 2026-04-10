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

// ID from router parameter passed into scope
$id = (int)$id;

// Fetch order
$order = Database::fetchOne(
    "SELECT o.*, u.name as customer_name, u.email as customer_email 
     FROM orders o 
     LEFT JOIN users u ON u.id = o.user_id 
     WHERE o.id = ?", 
    [$id]
);

if (!$order) {
    die("Đơn hàng không tồn tại.");
}

// Fetch order items
$items = Database::fetchAll(
    "SELECT oi.*, p.slug as product_slug 
     FROM order_items oi 
     LEFT JOIN products p ON p.id = oi.product_id 
     WHERE oi.order_id = ?", 
    [$id]
);

// Status config
$statusNames = [
    'pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý',
    'shipped'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy'
];
$statusColors = [
    'pending'=>'badge-yellow','confirmed'=>'badge-blue','processing'=>'badge-purple',
    'shipped'=>'badge-orange','delivered'=>'badge-green','cancelled'=>'badge-red'
];

$adminPageTitle = 'Chi tiết đơn hàng #' . $order['order_code'];
require __DIR__ . '/../app/Views/includes/admin_header.php';
?>

<div class="admin-header-actions">
    <a href="/admin/orders" class="btn btn-outline btn-sm">← Quay lại danh sách</a>
    <div class="actions">
        <span class="badge <?= $statusColors[$order['order_status']] ?>"><?= $statusNames[$order['order_status']] ?></span>
    </div>
</div>

<div class="admin-grid-details" style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; margin-top: 20px;">
    <!-- Main Info -->
    <div class="details-left">
        <div class="admin-card">
            <div class="card-header">
                <h3>Sản phẩm trong đơn hàng</h3>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['product_name']) ?></strong>
                            </td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td>x<?= $item['quantity'] ?></td>
                            <td><strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right"><strong>Tổng tiền hàng:</strong></td>
                            <td><strong><?= formatPrice($order['total']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="admin-card" style="margin-top: 24px">
            <div class="card-header">
                <h3>Ghi chú đơn hàng</h3>
            </div>
            <div class="card-body" style="padding: 20px">
                <p><?= e($order['notes'] ?: 'Không có ghi chú.') ?></p>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="details-right">
        <div class="admin-card">
            <div class="card-header">
                <h3>Thông tin giao hàng</h3>
            </div>
            <div class="card-body" style="padding: 20px; font-size: 0.9rem; line-height: 1.6">
                <p><strong>Người nhận:</strong> <?= e($order['shipping_name']) ?></p>
                <p><strong>Số điện thoại:</strong> <?= e($order['shipping_phone']) ?></p>
                <p><strong>Địa chỉ:</strong><br><?= nl2br(e($order['shipping_address'])) ?></p>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #eee">
                <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                <p><strong>Mã đơn hàng:</strong> <code>#<?= e($order['order_code']) ?></code></p>
            </div>
        </div>

        <div class="admin-card" style="margin-top: 24px">
            <div class="card-header">
                <h3>Thanh toán</h3>
            </div>
            <div class="card-body" style="padding: 20px; font-size: 0.9rem">
                <p><strong>Phương thức:</strong> <?= strtoupper(e($order['payment_method'])) ?></p>
                <p><strong>Trạng thái:</strong> 
                    <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-green' : 'badge-yellow' ?>">
                        <?= $order['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' ?>
                    </span>
                </p>
                <?php if ($order['vnpay_transaction_no']): ?>
                <p><small class="text-muted">Giao dịch: <?= e($order['vnpay_transaction_no']) ?></small></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../app/Views/includes/admin_footer.php'; ?>
