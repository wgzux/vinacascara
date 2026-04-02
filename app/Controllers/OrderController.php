<?php
namespace App\Controllers;

use Database;
use Auth;

class OrderController {
    public function myOrders() {
        Auth::requireLogin();
        $pageTitle = 'Đơn Hàng Của Tôi';

        $orders = Database::fetchAll(
            "SELECT o.*, 
                    COUNT(oi.id) as item_count
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.user_id = ?
             GROUP BY o.id
             ORDER BY o.created_at DESC",
            [Auth::id()]
        );

        foreach ($orders as &$order) {
            $order['items'] = Database::fetchAll(
                "SELECT * FROM order_items WHERE order_id = ?", 
                [$order['id']]
            );
        }
        unset($order);

        $statusLabels = [
            'pending'    => ['label' => 'Chờ xác nhận', 'class' => 'status-pending'],
            'confirmed'  => ['label' => 'Đã xác nhận', 'class' => 'status-confirmed'],
            'processing' => ['label' => 'Đang xử lý', 'class' => 'status-processing'],
            'shipped'    => ['label' => 'Đang giao', 'class' => 'status-shipped'],
            'delivered'  => ['label' => 'Đã giao', 'class' => 'status-delivered'],
            'cancelled'  => ['label' => 'Đã hủy', 'class' => 'status-cancelled'],
        ];

        return view('my-orders', [
            'pageTitle' => $pageTitle,
            'orders' => $orders,
            'statusLabels' => $statusLabels
        ]);
    }
}
