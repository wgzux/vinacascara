<?php
namespace App\Controllers;

use Database;

class HomeController {
    public function index() {
        $pageTitle = 'Trà Cascara – Thức uống từ vỏ cà phê Arabica';
        $pageDescription = 'Khám phá Vina Cascara – trà làm từ 100% vỏ trái cà phê Arabica Việt Nam, phơi thủ công, hương vị tự nhiên tinh tế.';

        $products = Database::fetchAll(
            "SELECT p.*, 
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(r.id) as review_count
             FROM products p
             LEFT JOIN reviews r ON r.product_id = p.id AND r.status = 'approved'
             WHERE p.featured = 1 AND p.status = 'active'
             GROUP BY p.id
             ORDER BY p.id ASC"
        );

        foreach ($products as &$product) {
            $product['images'] = Database::fetchAll(
                "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC",
                [$product['id']]
            );
        }
        unset($product);

        return view('home', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'products' => $products
        ]);
    }

    public function submitContact() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name    = trim($_POST['name'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            if ($name && $phone) {
                \Auth::setFlash('success', 'Cảm ơn bạn! Chúng tôi sẽ liên hệ trong thời gian sớm nhất.');
            } else {
                \Auth::setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
            }
        }
        redirect('/#contact');
    }
}
