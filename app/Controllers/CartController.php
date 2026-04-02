<?php
namespace App\Controllers;

use Cart;

class CartController {
    public function index() {
        $pageTitle = 'Giỏ Hàng';
        $items = Cart::getItems();
        $totals = Cart::total();

        return view('cart', [
            'pageTitle' => $pageTitle,
            'items' => $items,
            'totals' => $totals
        ]);
    }

    public function apiAdd() {
        header('Content-Type: application/json');
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $success = Cart::add($productId, $qty);
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Đã thêm vào giỏ hàng!' : 'Không thể thêm sản phẩm.',
            'cart_count' => Cart::count(),
        ]);
    }

    public function apiUpdate() {
        header('Content-Type: application/json');
        $cartId = (int)($_POST['cart_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        $success = Cart::update($cartId, $qty);
        $totals = Cart::total();
        echo json_encode([
            'success' => $success,
            'cart_count' => Cart::count(),
            'subtotal' => formatPrice($totals['subtotal']),
            'shipping' => $totals['shipping'] == 0 ? 'Miễn phí' : formatPrice($totals['shipping']),
            'total' => formatPrice($totals['total']),
        ]);
    }

    public function apiRemove() {
        header('Content-Type: application/json');
        $cartId = (int)($_POST['cart_id'] ?? 0);
        Cart::remove($cartId);
        $totals = Cart::total();
        echo json_encode([
            'success' => true,
            'cart_count' => Cart::count(),
            'subtotal' => formatPrice($totals['subtotal']),
            'total' => formatPrice($totals['total']),
        ]);
    }

    public function apiCount() {
        header('Content-Type: application/json');
        echo json_encode(['count' => Cart::count()]);
    }
}
