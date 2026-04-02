<?php
namespace App\Controllers;

use Database;
use Cart;
use Auth;
use VNPay;

class CheckoutController {
    public function index() {
        $pageTitle = 'Thanh Toán';
        $items = Cart::getItems();

        if (empty($items)) {
            redirect('/cart');
        }

        $totals = Cart::total();
        $currentUser = Auth::user();

        return view('checkout', [
            'pageTitle' => $pageTitle,
            'items' => $items,
            'totals' => $totals,
            'currentUser' => $currentUser
        ]);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/checkout');
        }

        if (!Auth::verifyCsrf()) {
            Auth::setFlash('error', 'Lỗi bảo mật. Vui lòng thử lại.');
            redirect('/checkout');
        }

        $items = Cart::getItems();
        if (empty($items)) {
            redirect('/cart');
        }
        $totals = Cart::total();
        $currentUser = Auth::user();

        $paymentMethod = in_array($_POST['payment_method'] ?? '', ['cod', 'vnpay']) 
            ? $_POST['payment_method'] : 'cod';
        
        $data = [
            'name'    => trim($_POST['shipping_name'] ?? ''),
            'phone'   => trim($_POST['shipping_phone'] ?? ''),
            'address' => trim($_POST['shipping_address'] ?? ''),
        ];

        $errors = [];
        if (!$data['name']) $errors[] = 'Vui lòng nhập tên người nhận.';
        if (!$data['phone']) $errors[] = 'Vui lòng nhập số điện thoại.';
        if (!$data['address']) $errors[] = 'Vui lòng nhập địa chỉ giao hàng.';

        if (empty($errors)) {
            try {
                Database::beginTransaction();

                $orderCode = 'VC' . date('Ymd') . strtoupper(substr(uniqid(), -6));
                
                $orderId = Database::insert('orders', [
                    'order_code'       => $orderCode,
                    'user_id'          => Auth::id(),
                    'guest_name'       => $currentUser ? null : $data['name'],
                    'guest_email'      => $currentUser ? null : ($_POST['guest_email'] ?? null),
                    'guest_phone'      => $currentUser ? null : $data['phone'],
                    'shipping_name'    => $data['name'],
                    'shipping_phone'   => $data['phone'],
                    'shipping_address' => $data['address'],
                    'shipping_province'=> trim($_POST['shipping_province'] ?? ''),
                    'shipping_district'=> trim($_POST['shipping_district'] ?? ''),
                    'subtotal'         => $totals['subtotal'],
                    'shipping_fee'     => $totals['shipping'],
                    'discount'         => 0,
                    'total'            => $totals['total'],
                    'payment_method'   => $paymentMethod,
                    'payment_status'   => 'pending',
                    'order_status'     => 'pending',
                    'notes'            => trim($_POST['notes'] ?? ''),
                ]);

                foreach ($items as $item) {
                    Database::insert('order_items', [
                        'order_id'      => $orderId,
                        'product_id'    => $item['product_id'],
                        'product_name'  => $item['name'],
                        'product_image' => $item['image'],
                        'price'         => $item['price'],
                        'quantity'      => $item['quantity'],
                        'subtotal'      => $item['subtotal'],
                    ]);
                    // Reduce stock
                    Database::query(
                        "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?",
                        [$item['quantity'], $item['product_id'], $item['quantity']]
                    );
                }

                Database::commit();
                Cart::clear();

                $_SESSION['last_order_id'] = $orderId;
                $_SESSION['last_order_code'] = $orderCode;

                if ($paymentMethod === 'vnpay') {
                    $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
                    $payUrl = VNPay::createPaymentUrl($order);
                    redirect($payUrl);
                } else {
                    redirect('/order-success');
                }

            } catch (\Exception $e) {
                Database::rollback();
                Auth::setFlash('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
                redirect('/checkout');
            }
        } else {
            foreach ($errors as $err) {
                Auth::setFlash('error', $err);
            }
            redirect('/checkout');
        }
    }

    public function success() {
        $orderId = $_SESSION['last_order_id'] ?? null;
        $orderCode = $_SESSION['last_order_code'] ?? null;

        if (!$orderId) {
            redirect('/');
        }

        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        if (!$order) {
            redirect('/');
        }

        unset($_SESSION['last_order_id'], $_SESSION['last_order_code']);

        return view('order-success', [
            'order' => $order,
            'orderCode' => $orderCode,
            'pageTitle' => 'Đặt Hàng Thành Công'
        ]);
    }

    public function vnpayReturn() {
        $result = VNPay::verifyReturn($_GET);
        $orderCode = $result['order_code'];

        $order = Database::fetchOne("SELECT * FROM orders WHERE order_code = ?", [$orderCode]);

        if (!$order) {
            Auth::setFlash('error', 'Không tìm thấy đơn hàng.');
            redirect('/my-orders');
        }

        if ($result['valid'] && $result['success']) {
            Database::update('orders', [
                'payment_status'          => 'paid',
                'order_status'            => 'confirmed',
                'vnpay_transaction_no'    => $result['transaction_no'],
            ], 'order_code = ?', [$orderCode]);

            $_SESSION['last_order_id'] = $order['id'];
            $_SESSION['last_order_code'] = $orderCode;
            Auth::setFlash('success', 'Thanh toán VNPay thành công!');
            redirect('/order-success');
        } else {
            // Payment failed - restore stock
            $orderItems = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['id']]);
            foreach ($orderItems as $item) {
                if ($item['product_id']) {
                    Database::query(
                        "UPDATE products SET stock = stock + ? WHERE id = ?",
                        [$item['quantity'], $item['product_id']]
                    );
                }
            }
            Database::update('orders', ['payment_status' => 'failed'], 'order_code = ?', [$orderCode]);
            Auth::setFlash('error', 'Thanh toán thất bại: ' . $result['message']);
            redirect('/cart');
        }
    }
}
